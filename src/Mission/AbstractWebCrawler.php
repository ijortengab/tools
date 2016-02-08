<?php

namespace IjorTengab\Mission;

use IjorTengab\ParseHTMLAdvanced;
use IjorTengab\Browser\Browser;
use IjorTengab\Mission\Exception\ExecuteException;
use IjorTengab\Mission\Exception\StepException;
use IjorTengab\Mission\Exception\VisitException;

abstract class AbstractWebCrawler extends AbstractMission
{

    /**
     * Jeda request http antar satu visit dengan visit lainnya.
     * Satuan dalam detik. Range 0~2 detik. Float.
     */
    public $visit_delay = 0.35;

    /**
     * Property tempat menampung object dari class ParseHtmlAdvanced.
     */
    protected $html;

    /**
     * Menyimpan hasil, akhir dari eksekusi.
     */
    public $result;

    /**
     * Property tempat menampung object dari class Browser.
     *
     * @see
     *   ::browserInit()
     */
    protected $browser;

    /**
     * Untuk keperluan debug. Jika true, maka informasi akses log dan cache
     * saat request http, akan disimpan sebagai file text.
     */
    protected $debug = true;

    /**
     * Override method.
     *
     * Set property information in object.
     *
     * @param $property string
     *   Parameter dapat bernilai sebagai berikut:
     *   - username
     *     Username for login.
     *   - password
     *     Password for login.
     *   - account
     *     Account Number.
     *   dan property lainnya yang dijelaskan pada parent::set().
     */
    public function set($property, $value)
    {
        switch ($property) {
            case 'debug':
            case 'visit_delay':
                $this->{$property} = $value;
                break;
            case 'browser_cookie':
            case 'browser_history':
            case 'browser_response_body':
            case 'browser_cwd':
                $this->configuration('temporary][browser][' . $property, $value);
                break;
        }
        return parent::set($property, $value);
    }

    /**
     * Handler bawaan Abstract WebCrawler.
     *
     * Method untuk melakukan request http sesuai dengan definisi pada menu
     * dalam property $step.
     */
    protected function visit()
    {
        try {
            if (null === $this->browser) {
                $this->browserInit();
            }
            // Prepare.
            $menu_name = isset($this->step['menu']) ? $this->step['menu'] : null;
            if (empty($menu_name)) {
                $this->log->error('Menu information in "Step Definition" has not been defined.');
                throw new VisitException;
            }
            $menu_information = $this->configuration("menu][$menu_name");
            if (empty($menu_information)) {
                $this->log->error('Information for menu "{menu}" has not been defined.', ['menu' => $menu_name]);
                throw new VisitException;
            }
            $url = $this->configuration("menu][$menu_name][url");
            if (empty($url)) {
                $this->log->error('URL for menu "{menu}" has not been defined.', ['menu' => $menu_name]);
                throw new VisitException;
            }

            // Reset browser and set new URL.
            $this->browser->reset()->setUrl($url);

            // Set referer.
            $referer = $this->configuration('referer');
            if (!empty($referer)) {
                $this->browser->headers('Referer', $referer);
            }

            // Play with post request.
            $fields = (array) $this->configuration("menu][$menu_name][fields");
            if (!empty($fields)) {
                $this->browser->post($fields);
                // Clear information of fields.
                $this->configuration("menu][$menu_name][fields", null);
            }

            // Execute.
            $this->visitBefore();
            $this->browser->execute();
            $this->log->debug('Mengunjungi menu {name} ({time} detik).', ['name' => $menu_name, 'time' => $this->browser->timer->read()]);
            $this->configuration('last_visit', date('c'));
            // Simpan referer untuk next browsing, gunakan ::getUrl alih-alih
            // $url karena ada kemungkinan terjadi redirect.
            $this->configuration('referer', $this->browser->getUrl($url));
            $this->visitAfter();

            // Everything's OK, and wait for delay.
            // Beri jeda antara 0 sampai 2 detik.
            if ($this->visit_delay > 0 && $this->visit_delay <= 2) {
                $this->visit_delay *= 1000000;
                usleep($this->visit_delay);
            }
        }
        catch (VisitException $e) {
            $this->log->debug('VisitException. Result not expected.');
            // Paksa stop.
            throw new ExecuteException;
        }
    }

    protected function visitBefore()
    {
        if (isset($this->step['visit_before'])) {
            $this->runMethod($this->step['visit_before'], true);
        }
    }

    protected function visitAfter()
    {
        if (isset($this->step['visit_after'])) {
            $this->runMethod($this->step['visit_after'], true);
        }
    }

    /**
     * Memulai instance object dari class Browser.
     */
    protected function browserInit()
    {
        $settings = $this->configuration('temporary][browser');

        if (!empty($settings['browser_cwd'])) {
            $browser_cwd = $this->cwd->getAbsolutePath($settings['browser_cwd']);
            $cwd = new WorkingDirectory($browser_cwd, $this->log);
        }
        else {
            $cwd = $this->cwd;
        }

        $this->browser = new Browser(null, $this->log);
        $this->browser->setCwd($cwd);

        // User Agent.
        $user_agent = $this->configuration('user_agent');
        if (empty($user_agent)) {
            $user_agent = $this->browser->getUserAgent('Desktop');
            $this->configuration('user_agent', $user_agent);
        }
        // Default Options.
        $this->browser
            ->options('cookie_receive', true)
            ->options('cookie_send', true)
            ->options('follow_location', true)
            ->options('user_agent', $user_agent)
        ;
        // Other Settings.
        if (!empty($settings['browser_cookie'])) {
            $this->browser->cookie_filename = $settings['browser_cookie'];
        }
        if (!empty($settings['browser_history'])) {
            $this->browser->history_filename = $settings['browser_history'];
        }
        if (!empty($settings['browser_response_body'])) {
            $this->browser->_response_body_filename = $settings['browser_response_body'];
        }
        // If debug true.
        if ($this->debug) {
            $this->browser
                ->options('history_save', true)
                ->options('response_body_save', true)
            ;
        }
    }

    protected function verify()
    {
        // Harus ada informasi verifikasi pada menu.
        $menu_name = $this->step['menu'];
        $verify = (array) $this->configuration("menu][$menu_name][verify");
        if (empty($verify)) {
            return;
        }
        if (empty($this->browser->result->data)) {
            $this->log->error('Empty html data.');
            throw new VisitException;
        }
        // Use ParseHtml.
        $this->html = new ParseHTMLAdvanced($this->browser->result->data);
        $context_founded = false;
        foreach ($verify as $indication_name => $handler) {
            if ($this->checkIndication($indication_name)) {
                $this->log->debug('Indikasi {name} ditemukan.', ['name' => $indication_name]);
                $this->runMethod($handler, true);
                $context_founded = true;
                break;
            }
        }
        if (!$context_founded) {
            $this->log->error('Verifikasi menu {menu} gagal, layout kemungkinan mengalami perubahan.', ['menu' => $menu_name]);
            throw new VisitException;
        }
    }

    /**
     *
     */
    protected function checkIndication($indication_name) {}
}
