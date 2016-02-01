<?php

namespace IjorTengab\Mission;

use IjorTengab\ParseInfo;
use IjorTengab\FileSystem\WorkingDirectory;
use IjorTengab\ObjectHelper\PropertyArrayManagerTrait;
use IjorTengab\ObjectHelper\CamelCase;
use IjorTengab\Logger\Log;
use IjorTengab\Mission\Exception\ExecuteException;
use IjorTengab\Mission\Exception\StepException;

abstract class AbstractMission
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
     * Jeda execute antar satu step dengan step lainnya.
     * Satuan dalam detik. Range 0~2 detik. Float.
     */
    public $step_delay = 0;

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
     * Child class must declare own configuration.
     */
    abstract public function defaultConfiguration();

    /**
     * Child class must define current working directory.
     */
    abstract public function defaultCwd();

    /**
     * Construct.
     */
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
            case 'target':
            case 'step_delay':
                $this->{$property} = $value;
                break;
            case 'configuration':
                $this->configuration_custom_filename = $value;
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
    protected function addStep($position, Array $steps)
    {
        switch ($position) {
            case 'prepend':
                $this->log->notice('Sebanyak {c} step ditambahkan prepend.', ['c' => count($steps)]);
                $this->steps = array_merge($steps, $this->steps);
                break;

            case 'append':
                $this->log->notice('Sebanyak {c} step ditambahkan append.', ['c' => count($steps)]);
                $this->steps = array_merge($this->steps, $steps);
                break;

            default:
                $this->log->error('Position {name} tidak valid', ['name' => $position]);
                throw new ExecuteException;
                break;
        }
    }

    protected function addStepFromReference($reference_name)
    {
        $ref = $this->configuration("reference][$reference_name");
        if (null === $ref || !is_array($ref) || !array_key_exists('position', $ref) || !array_key_exists('steps', $ref)) {
            $this->log->error('Reference {name} tidak valid', ['name' => $reference_name]);
            throw new ExecuteException;
        }
        $this->addStep($ref['position'], $ref['steps']);
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
                // Jika handler_before ingin menghentikan proses current step,
                // maka caranya throw ke StepException.
                // Jika handler ingin menghentikan keseluruhan proses,
                // maka caranya throw ke ExecuteException.
                try {
                    // Jalankan handler.
                    if (isset($this->step['handler'])) {
                        $this->handlerBefore();
                        $this->runMethod($this->step['handler'], true);
                        $this->handlerAfter();
                    }
                    // Beri jeda antara 0 sampai 2 detik.
                    if ($this->step_delay > 0 && $this->step_delay <= 2) {
                        $this->step_delay *= 1000000;
                        usleep($this->step_delay);
                    }
                }
                catch (StepException $e) {
                    $this->log->notice('StepException. Current Step process is skipped.');
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

    protected function handlerBefore()
    {
        if (isset($this->step['handler_before'])) {
            $this->runMethod($this->step['handler_before'], true);
        }
    }

    protected function handlerAfter()
    {
        if (isset($this->step['handler_after'])) {
            $this->runMethod($this->step['handler_after'], true);
        }
    }

    /**
     * Menjalankan handler (method) jika exists.
     *
     * @param $handlers array
     *   Kumpulan method sebagai handler, posisi pertama akan dijalankan,
     *   jika handler tidak exists, maka akan dijalankan handler berikutnya.
     */
    protected function runMethod($handlers, $execute_alternative = false)
    {
        $handlers = (array) $handlers;
        foreach ($handlers as $method) {
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method));
            }
            elseif ($execute_alternative && method_exists($this, CamelCase::convertFromUnderScore($method))) {
                call_user_func(array($this, CamelCase::convertFromUnderScore($method)));
            }
        }
    }

    /**
     * Handler bawaan Abstract Mission.
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
}
