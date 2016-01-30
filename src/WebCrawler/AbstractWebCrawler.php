<?php

namespace IjorTengab\WebCrawler;

use IjorTengab\ParseInfo;
use IjorTengab\ParseHTMLAdvanced;
use IjorTengab\Browser\Browser;
use IjorTengab\FileSystem\WorkingDirectory;
use IjorTengab\ObjectHelper\PropertyArrayManagerTrait;
use IjorTengab\ObjectHelper\CamelCase;
use IjorTengab\Logger\Log;

/**
 *
 * IjorTengab's Web Crawler.
 *
 * @file
 *   AbstractWebCrawler.php
 *
 * @author
 *   IjorTengab
 *
 * @homepage
 *   https://github.com/ijortengab/tools
 *
 * @version
 *   0.0.4
 *
 * Abstract yang menyediakan pola kerja untuk crawling pada halaman web.
 * Disesuaikan dengan "behaviour" manusia saat browsing menggunakan browser.
 *
 * Abstract ini membutuhkan
 *   - class Browser (ijortengab/browser),
 *   - class ParseHtml (ijortengab/parse-html),
 *   - class ParseInfo (ijortengab/parse-info),
 *   - Todo.
 *
 * Tambahkan require berikut pada composer.json bila abstract ini digunakan
 * dalam project anda.
 *
 * ```json
 *
 *     "require": {
 *         "ijortengab/browser": ">=0.0.7",
 *         "ijortengab/parse-html": ">=0.0.5",
 *         "ijortengab/info-configuration": ">=0.0.3"
 *     }
 *
 * ```
 *
 * Abstract ini tidak memiliki repository mandiri, hadir (shipped) bersama
 * project lain. Untuk melihat perkembangan dari abstract ini bisa dilihat
 * pada @homepage.
 *
 */
abstract class AbstractWebCrawler
{
    /**
     * Loading traits.
     */
    use PropertyArrayManagerTrait;

    /**
     * Current Working Directory (cwd), dibuat terpisah dengan cwd milik PHP.
     */
    public $cwd;

    /**
     * String, tujuan utama yang menjadi acuan dari setiap step yang berjalan.
     */
    public $target;

    /**
     * Array, berisi informasi langkah kerja yang sedang berlangsung.
     */
    protected $step;

    /**
     * Array, berisi kumpulan langkah kerja untuk memenuhi kebutuhan
     * achievement dari property $target.
     */
    protected $steps;

    /**
     * Property yang menjadi acuan pada akhir eksekusi handler setiap step
     * yang telah berjalan. Biasanya handler mengubah nilai ini menjadi true
     * jika gagal dalam verifikasi, sehingga keseluruhan proses harus
     * dihentikan.
     */
    protected $execute_stop = false;

    /**
     * Jeda execute antar satu step dengan step lainnya.
     * Satuan dalam detik. Range 0~2 detik. Float.
     */
    public $step_delay = 0;

    /**
     * Jeda request http antar satu visit dengan visit lainnya.
     * Satuan dalam detik. Range 0~2 detik. Float.
     */
    public $visit_delay = 0.35;

    /**
     * Untuk keperluan debug. Jika true, maka informasi akses log dan cache
     * saat request http, akan disimpan sebagai file text.
     */
    public $debug = true;

    /**
     * Property tempat menampung object dari class ParseHtmlAdvanced.
     */
    protected $html;

    /**
     * Menyimpan hasil, akhir dari eksekusi.
     */
    public $result;

    /**
     * Property tempat menampung log yang terjadi selama proses.
     * Hanya dua tipe log: notice dan error.
     */
    public $log;

    /**
     * Array tempat menampung nilai konfigurasi. Property ini akan
     * mulai diisi saat dijalankan method self::configurationInit()
     */
    protected $configuration;

    /**
     * Array, tempat menampung segala perubahan dari property $configuration.
     * Jika saat object ini di destruct dan nilai dari
     * property ini tidak empty, maka nilai property ini akan di dump
     * sebagai file text untuk digunakan pada proses berikutnya.
     *
     * @see
     *   self::configuration()
     *   self::__destruct()
     */
    protected $configuration_custom;

    /**
     * Nama file untuk menyimpan konfigurasi custom. Isi file ini merupakan
     * string hasil encode menggunakan fungsi ParseInfo::encode().
     */
    public $configuration_custom_filename = 'configuration.info';

    /**
     * Penanda bahwa file $configuration_custom_filename exists.
     */
    protected $configuration_custom_file_is_exists;

    /**
     * Penanda bahwa file $configuration_custom_filename telah mengalami
     * perubahan.
     */
    protected $configuration_custom_file_has_changed;

    /**
     * Property tempat menampung object dari class Browser.
     *
     * @see
     *   ::browserInit()
     */
    protected $browser;

    /**
     * Child class must declare own configuration.
     */
    abstract public function defaultConfiguration();

    /**
     * Child class must define current working directory.
     */
    abstract public function defaultCwd();

    public function __construct()
    {
        // Init log.
        $this->log = new Log;

        // Cwd must initialize when construct.
        $this->cwd = new WorkingDirectory($this->defaultCwd(), $this->log);

        $this->configurationInit();

        $this->init();
    }

    public function __destruct()
    {
        // Jangan simpan informasi temporary (jika ada) sebagai
        // custom configuration.
        $this->configuration('temporary', null);
        $this->configurationDump();
    }

    // Init berjalan setelah default configuration berhasil masuk ke property.
    // Gunakaan ini untuk menambah/mengubah configurasi awal.
    protected function init() {}

    /**
     * Set property information in object.
     *
     * @param $property string
     *   Parameter dapat bernilai sebagai berikut:
     *   - debug
     *     If true, maka akan dibuat file cache html hasil request dan catatan
     *     history request.
     *   - delay
     *     Jeda antara satu step dengan step lainnya.
     *   - target
     *     Misi utama dari object ini. Definisi step dari target di definisikan
     *     pada ::defaultConfiguration().
     *   - configuration
     *     Nama file dari configuration. Bisa berupa basename atau fullpath.
     *   - cookie
     *     Nama file dari cookie. Bisa berupa basename atau fullpath.
     *   - history
     *     Nama file dari history. Bisa berupa basename atau fullpath.
     *   - cache
     *     Nama file dari cache. Bisa berupa basename atau fullpath.
     *   - cwd
     *     Set current working directory.
     */
    public function set($property, $value)
    {
        switch ($property) {
            case 'debug':
            case 'step_delay':
            case 'visit_delay':
            case 'target':
                $this->{$property} = $value;
                break;
            case 'configuration':
                $this->configuration_custom_filename = $value;
                break;
            case 'browser_cookie':
            case 'browser_history':
            case 'browser_response_body':
            case 'browser_cwd':
                $this->configuration('temporary][browser][' . $property, $value);
                break;
            case 'cwd':
                $this->cwd->chDir($value);
                break;
        }
        return $this;
    }

    /**
     * Menambahkan steps.
     */
    protected function addStep($position, $steps)
    {
        switch ($position) {
            case 'prepend':
                $this->steps = array_merge($steps, $this->steps);
                break;

            case 'append':
                $this->steps = array_merge($this->steps, $steps);
                break;
        }
    }

    /**
     * Method for retrieve and update property $configuration.
     * Setiap ada perubahan nilai configuration dari default, maka
     * perubahan tersebut di copy ke property $configuration_custom.
     * Nantinya setiap eksekusi selesai, maka property dari
     * $configuration_custom akan disimpan sebagai file text untuk
     * dipakai pada eksekusi berikutnya.
     *
     * @see ::configurationDump()
     */
    protected function configuration()
    {
        $args = func_get_args();
        // Jika argument lebih dari 1, maka itu berarti create/update
        // configuration, maka kita simpan perubahan dalam custom.
        // Nantinya akan disimpan sebagai perubahan.
        if ($args > 1) {
            $this->configuration_custom_file_has_changed = true;
            $this->propertyArrayManager('configuration_custom', $args);
        }
        return $this->propertyArrayManager('configuration', $args);
    }

    /**
     * Start loading configuration from default (that defined by
     * self::defaultConfiguration() and from custom (that stored in
     * file configuration).
     */
    protected function configurationInit()
    {
        $filename = $this->cwd->getAbsolutePath($this->configuration_custom_filename);
        $this->cwd->addFile($this->configuration_custom_filename);
        $custom = array();
        $this->configuration_custom_file_is_exists = $file_exists = file_exists($filename);
        if ($file_exists) {
            $custom = ParseInfo::decode(file_get_contents($filename));
            $this->configuration_custom = $custom;
        }
        $default = $this->defaultConfiguration();
        $this->configuration = array_replace_recursive($default, $custom);
    }

    /**
     * Setiap ada configuration yang diubah sehingga nilai nya berbeda dengan
     * nilai default, maka perubahan variable configuraton tersebut akan
     * disimpan untuk digunakan pada pekerjaan (eksekusi) berikutnya.
     * Konfigurasi disimpan sebagai file text yang mana nama file didefinisikan
     * oleh property $configuration_custom_filename;
     */
    protected function configurationDump()
    {
        $filename = $this->cwd->getAbsolutePath($this->configuration_custom_filename);
        $file_exists = $this->configuration_custom_file_is_exists;

        if ($file_exists && empty($this->configuration_custom)) {
            // Todo, gunakan try and catch.
            @unlink($filename);
        }

        if($this->configuration_custom_file_has_changed) {
            if (!empty($this->configuration_custom)) {
                $contents = ParseInfo::encode($this->configuration_custom);
                file_put_contents($filename, $contents);
            }
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

    /**
     * Main function.
     */
    public function execute()
    {
        try {
            $target = $this->target;
            if (empty($target)) {
                $this->log->error('Target has not been defined.');
                throw new ExecuteException;
            }
            $steps = $this->configuration('target][' . $target);
            if (empty($steps)) {
                $this->log->error('Steps definition for target {target} has not been defined.', ['target' => $target]);
                throw new ExecuteException;
            }

            $this->steps = $steps;

            $this->executeBefore();
            // Run.
            while ($this->step = array_shift($this->steps)) {

                // Jalankan handler.
                $handler = [];
                // Priority from key handler, alternative from key type.
                !isset($this->step['handler']) or $handler[] = $this->step['handler'];
                !isset($this->step['type']) or $handler[] = $this->step['type'];
                // Jalankan hanya sekali saja, beri true pada argument ketiga.
                $this->executeHandler($handler, true, true);
                // Jika ada handler yang memaksa stop.
                if ($this->execute_stop) {
                    break;
                }
                // Beri jeda antara 0 sampai 2 detik.
                if ($this->step_delay > 0 && $this->step_delay <= 2) {
                    $this->step_delay *= 1000000;
                    usleep($this->step_delay);
                }
            }
            $this->executeAfter();
        }
        catch (ExecuteException $e) {
            $this->log->notice('ExecuteException. Execution is stopped.');
        }

        return $this;
    }

    protected function executeBefore() {}

    protected function executeAfter() {}

    /**
     * Menjalankan handler (method) jika exists.
     *
     * @param $handlers array
     *   Kumpulan method sebagai handler, posisi pertama akan dijalankan,
     *   jika handler tidak exists, maka akan dijalankan handler berikutnya.
     */
    protected function executeHandler($handlers, $execute_alternative = false, $once_only = false)
    {
        $handlers = (array) $handlers;
        $run = false;
        foreach ($handlers as $method) {
            if (method_exists($this, $method)) {
                $run = true;
                call_user_func(array($this, $method));
            }
            elseif ($execute_alternative && method_exists($this, CamelCase::convertFromUnderScore($method))) {
                $run = true;
                call_user_func(array($this, CamelCase::convertFromUnderScore($method)));
            }
            if ($once_only && $run) {
                break;
            }
        }
    }

    /**
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
            $url = $this->configuration('menu][' . $menu_name . '][url');
            if (empty($url)) {
                $this->log->error('URL information for menu "{menu}" has not been defined.', ['menu' => $menu_name]);
                throw new VisitException;
            }
            // Reset browser and set new URL.
            $this->browser->reset()->setUrl($url);
            // Play with referer.
            $referer = $this->configuration('referer');
            if (!empty($referer)) {
                $this->browser->headers('Referer', $referer);
            }
            $this->configuration('referer', $url);
            // Play with post request.
            $fields = (array) $this->configuration('menu][' . $menu_name . '][fields');
            if (!empty($fields)) {
                $this->browser->post($fields);
                // Clear information of fields.
                $this->configuration('menu][' . $menu_name . '][fields', null);
            }

            // Execute.
            $this->visitBefore();
            $this->browser->execute();
            $this->visitAfter();

            // Run Verify.
            $verify = $this->configuration('menu][' . $menu_name . '][verify');
            if (!empty($this->step['must_verify']) && !empty($verify)) {
                if (empty($this->browser->result->data)) {
                    $this->log->error('Empty html data.');
                    throw new VisitException;
                }
                // Use ParseHtml.
                $this->html = new ParseHTMLAdvanced($this->browser->result->data);
                $context_founded = false;
                foreach ($verify as $indication_name => $handler) {
                    if ($this->checkIndication($indication_name)) {
                        $this->executeHandler($handler, true);
                        $context_founded = true;
                        break;
                    }
                }
                if (!$context_founded) {
                    $this->log->error('Verifikasi menu {menu} gagal, layout kemungkinan mengalami perubahan.', ['menu' => $menu_name]);
                    throw new VisitException;
                }
            }

            // Everything's OK, and wait for delay.
            // Beri jeda antara 0 sampai 2 detik.
            if ($this->visit_delay > 0 && $this->visit_delay <= 2) {
                $this->visit_delay *= 1000000;
                usleep($this->visit_delay);
            }
        }
        catch (VisitException $e) {
            $this->log->error('VisitException. Result not expected. Execution is stopped.');
            $this->execute_stop = true;
        }
    }

    protected function visitBefore() {}

    protected function visitAfter() {}

    /**
     *
     */
    protected function checkIndication($indication_name) {}

    /**
     *
     */
    protected function resetExecute()
    {
        $this->resetExecuteBefore();
        $target = $this->target;
        $this->steps = $this->configuration('target][' . $target);
        $this->log->notice('Reset Execute.');
        $this->resetExecuteAfter();
    }

    protected function resetExecuteBefore() {}

    protected function resetExecuteAfter() {}

    /**
     * Todo.
     */
    protected function reportError() {}
}
