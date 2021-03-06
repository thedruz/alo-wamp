<?php

   namespace Setup;

   use Service;
   use Settings as SET;

   /**
    * Sets up Redis
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class Redis extends AbstractBinSetup {

      /**
       * BinChecker instance
       *
       * @var \BinChecker\Redis
       */
      protected $binchecker;

      /**
       * Constructor
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      function __construct() {
         $this->dest       = DIR_TMP . 'redis.zip';
         $this->dest_unzip = DIR_TMP . 'redis' . DIRECTORY_SEPARATOR;

         $this->binchecker = new \BinChecker\Redis();
         $this->links      = $this->binchecker->getLinks();

         $this
            ->promptDownload()
            ->unzip()
            ->copy()
            ->editConf()
            ->installService();
      }

      /**
       * Installs the service
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Redis
       */
      protected function installService() {
         if(Service::exists('aloredis')) {
            _echo('Removing previous AloWAMP Redis service');
            _echo(Service::delete('aloredis'));
         }

         _echo('Installing Redis service');
         _echo(Service::installExe('aloredis',
                                   DIR_REDIS .
                                   $this->version .
                                   DIRECTORY_SEPARATOR .
                                   'redis-server.exe --service-run --service-name aloredis',
                                   'AloWAMP Redis ' . $this->version));

         return $this;
      }

      /**
       * Edits the config file
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Redis
       */
      protected function editConf() {
         $file = DIR_REDIS . $this->version . DIRECTORY_SEPARATOR . 'redis.windows.conf';

         if(!file_exists($file)) {
            die('Redis configuration file not found. Aborting.');
         } else {
            _echo('Editing Redis configuration file');
            $contents = file_get_contents($file);

            $contents = str_ireplace([
                                        '# bind 127.0.0.1',
                                        'tcp-keepalive 0',
                                        'logfile ""'
                                     ],
                                     [
                                        'bind 127.0.0.1',
                                        'tcp-keepalive 60',
                                        'logfile "' . DIR_LOGS . 'redis' . DIRECTORY_SEPARATOR . 'redis.log"'
                                     ],
                                     $contents);

            if(file_put_contents($file, $contents) !== false) {
               _echo('Redis config file edited');
            } else {
               die('Failed to edit Redis config file. Abording.');
            }
         }

         return $this;
      }

      /**
       * Copies unzipped contents
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Redis
       */
      protected function copy() {
         _echo('Copying unzipped contents..');

         $source                     = rtrim($this->dest_unzip, DIRECTORY_SEPARATOR);
         $this->unzipped_destination = DIR_REDIS . $this->version;

         if(!file_exists($this->unzipped_destination)) {
            mkdir($this->unzipped_destination . DIRECTORY_SEPARATOR, 777, true);
         }

         shell_exec('xcopy /s /e "' . $source . '" "' . $this->unzipped_destination . '"');

         $this->unzipped_destination .= DIRECTORY_SEPARATOR;

         if(file_exists($this->unzipped_destination . 'redis-server.exe')) {
            _echo('Copy successful!');
            $this->updateSettings();
            $this->cleanup();
         } else {
            die('Failed to copy. Terminating setup.');
         }

         return $this;
      }

      /**
       * Updates settings.ini
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Redis
       */
      protected function updateSettings() {
         SET::$s->redis_version = $this->version;
         SET::$s->save();

         return $this;
      }
   }