<?php

use \PHPUnit\Framework\Assert;
use \YesTicket\Model\CachedImage;

function assertTranslate(&$wasCalled, $expectedInput)
{
  $wasCalled = false;
  \remove_all_filters('gettext', 69);
  \add_filter('gettext', function ($translated_text, $untranslated_text, $domain) use (&$wasCalled, $expectedInput) {
    $wasCalled = true;
    Assert::assertSame($domain, 'yesticket');
    Assert::assertSame($expectedInput, $untranslated_text);
    return $translated_text;
  }, 69, 3);
}


/**
 * @param callable $renderer \imagexxxx call
 * @param int $qualityArg 3rd param (quality) used in \imagexxxx call
 * @return CachedImage image
 */
function getCachedImage($type, $renderer, $qualityArg = 0)
{
  $image = \imagecreatetruecolor(10, 10);
  \ob_start();
  Assert::assertTrue($renderer($image, null, $qualityArg), 'should be able to create image');
  return new CachedImage($type, \ob_get_clean());
}

class LogCapture
{
  static private $instance;

  static private function getInstance()
  {
    if (!isset(LogCapture::$instance)) {
      LogCapture::$instance = new LogCapture();
    }
    return LogCapture::$instance;
  }

  private $tmpFile;
  private $oldLocation;
  private $open = false;

  /**
   * Redirect error_log to our capturing
   */
  static public function start()
  {
    $lc = LogCapture::getInstance();
    Assert::assertFalse($lc->open, ">>> LogCapture -> Capturing already ongoing!");
    $lc->open = true;
    $lc->tmpFile = tmpfile();
    $lc->oldLocation = ini_set('error_log', stream_get_meta_data($lc->tmpFile)['uri']);
  }
  /**
   * Restore error_log to normal state and return captured content
   * @return string content
   */
  static public function end_get()
  {
    $lc = LogCapture::getInstance();
    Assert::assertTrue($lc->open, ">>> LogCapture -> Capturing was not running!");
    $result = stream_get_contents($lc->tmpFile);
    ini_set('error_log', $lc->oldLocation);
    $lc->open = false;
    return $result;
  }
  function __destruct()
  {
    if ($this->open) {
      LogCapture::end_get();
      throw new AssertionError('>>> LogCapture -> NOT CLOSED! YOUR LOGGING MAY BE INCONSISTENT. Please verify you called ::end_get for every ::start');
    }
  }
}

/**
 * Utility class providing some html testing capabilities
 */
abstract class YTP_HtmlTestCase extends \WP_UnitTestCase
{
  /**
   * Close HTML <tag> that are valid without their counterpart </tag>
   * 
   * @param string $input
   * 
   * Closes:
   *  * <input />
   *  * <img />
   * 
   * Useful to test HTML output via @see \simplexml_load_string
   * 
   */
  function closeStandaloneHtmlTags($input)
  {
    return \preg_replace('/(<(input|img)[\s\w"\'=\/\[\]]+[\s\w"\'=\[\]])>/', '${1}/>', $input);
  }

  function validateAndGetAsXml($input) {
    \libxml_clear_errors();
    $asXML = \simplexml_load_string($this->closeStandaloneHtmlTags($input));
    $this->assertEmpty(libxml_get_errors(), "Should produce valid HTML, but is: >>> \n" . $asXML->asXML());
    return $asXML;
  }
}

/**
 * Utility class to test with translations
 */
abstract class YTP_TranslateTestCase extends \YTP_HtmlTestCase
{
  protected $assertedTranslations = [];
  protected $actualTranslations = [];

  public function set_up(): void
  {
    parent::set_up();
    $this->assertedTranslations = [];
    $this->actualTranslations = [];
    \remove_all_filters('gettext', 69);
    \add_filter('gettext', function ($translated_text, $untranslated_text, $domain) {
      $this->actualTranslations[] = array('text' => $untranslated_text, 'domain' => $domain);
      return $translated_text;
    }, 69, 3);
  }

  /**
   * Set up expected translations
   */
  public function expectTranslate($expectedInput, $domain = 'yesticket')
  {
    $this->assertedTranslations[] = array('text' => $expectedInput, 'domain' => $domain);
  }

  public function assert_post_conditions(): void
  {
    parent::assert_post_conditions();
    \remove_all_filters('gettext', 69);
    $this->assertSameSets($this->assertedTranslations, $this->actualTranslations, "Expected different translations.");
  }
}

/**
 * Utility class to test our templates.
 */
abstract class YTP_TemplateTestCase extends \YTP_TranslateTestCase
{
  /**
   * Return the template for a template test.
   * This only works if directory structure and the test's filename follow the correct pattern.
   * 
   * @param string __FILE__
   * @return string templatePath
   */
  public function getTemplatePath($file)
  {
    return str_replace('-test.php', '.php', str_replace('tests', 'src', $file));
  }

  /**
   * Get templated XML for a template test
   * This only works if directory structure and the test's filename follow the correct pattern.
   * 
   * @param string __FILE__
   * @param array $variables passed via 'compact', to be used via 'extract'
   * @return SimpleXMLElement
   * 
   * @see https://www.w3schools.com/xml/xpath_syntax.asp to select XML node to run assertions.
   */
  public function includeTemplate($file, $variables = array())
  {
    $template_path = $this->getTemplatePath($file);
    if (!is_readable($template_path)) {
      throw new \Error("Cannot read template file '$template_path'");
    }
    // Extract the variables to a local namespace
    \extract($variables);
    \ob_start();
    include $template_path;
    $result = \ob_get_clean();
    $this->assertNotEmpty($result);
    return $this->validateAndGetAsXml($result);
  }
}
