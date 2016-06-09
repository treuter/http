<?php namespace peer\http\unittest;

use peer\http\HttpProxy;
use peer\http\proxy\EnvironmentSettings;

/**
 * Verifies inferring proxy settings from the environment
 *
 * @see    xp://peer.http.proxy.EnvironmentSettings
 */
class EnvironmentSettingsTest extends \unittest\TestCase {
  private $environment;

  /**
   * Unsets all relevant variables
   *
   * @return void
   */
  public function setUp() {
    $this->environment= new Environment([
      'http_proxy'  => null,
      'HTTP_PROXY'  => null,
      'https_proxy' => null,
      'HTTPS_PROXY' => null,
      'no_proxy'    => null,
      'NO_PROXY'    => null,
      'all_proxy'   => null,
      'ALL_PROXY'   => null
    ]);
  }

  /**
   * Restores environment
   *
   * @return void
   */
  public function tearDown() {
    $this->environment->close();
  }

  #[@test, @values(['http', 'https'])]
  public function no_proxy($scheme) {
    $this->assertEquals(HttpProxy::NONE, (new EnvironmentSettings())->proxy($scheme));
  }

  #[@test, @values(['no_proxy', 'NO_PROXY'])]
  public function excludes_from_no_proxy($env) {
    with (new Environment([$env => '*']), function() {
      $this->assertEquals(['*'], (new EnvironmentSettings())->excludes());
    });
  }

  #[@test, @values(['no_proxy', 'NO_PROXY'])]
  public function excludes_when_unset($env) {
    with (new Environment([$env => null]), function() {
      $this->assertEquals([], (new EnvironmentSettings())->excludes());
    });
  }

  #[@test, @values(['no_proxy', 'NO_PROXY'])]
  public function excludes_when_empty($env) {
    with (new Environment([$env => '']), function() {
      $this->assertEquals([], (new EnvironmentSettings())->excludes());
    });
  }

  #[@test, @values(['http_proxy', 'HTTP_PROXY'])]
  public function http_proxy($env) {
    with (new Environment([$env => 'proxy.example.com:3128']), function() {
      $proxy= (new EnvironmentSettings())->proxy('http');
      $this->assertEquals(['proxy.example.com', 3128], [$proxy->host(), $proxy->port()]);
    });
  }

  #[@test, @values(['https_proxy', 'HTTPS_PROXY'])]
  public function https_proxy($env) {
    with (new Environment([$env => 'proxy.example.com:3128']), function() {
      $proxy= (new EnvironmentSettings())->proxy('https');
      $this->assertEquals(['proxy.example.com', 3128], [$proxy->host(), $proxy->port()]);
    });
  }

  #[@test, @values(['all_proxy', 'ALL_PROXY'])]
  public function all_proxy($env) {
    with (new Environment([$env => 'proxy.example.com:3128']), function() {
      $proxy= (new EnvironmentSettings())->proxy('http');
      $this->assertEquals(['proxy.example.com', 3128], [$proxy->host(), $proxy->port()]);
    });
  }

  #[@test, @values([
  #  'proxy.example.com:3128',
  #  'http://proxy.example.com:3128',
  #  'http://proxy.example.com:3128/',
  #  ' http://proxy.example.com:3128 ',
  #  ' proxy.example.com:3128 ',
  #  ' http://proxy.example.com:3128/ '
  #])]
  public function proxy_formats($value) {
    with (new Environment(['http_proxy' => $value]), function() {
      $proxy= (new EnvironmentSettings())->proxy('http');
      $this->assertEquals(['proxy.example.com', 3128], [$proxy->host(), $proxy->port()]);
    });
  }
}
