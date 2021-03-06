<?php

   namespace Setup;

   use Downloader;

   /**
    * Abstract setup for binaries
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   abstract class AbstractBinSetup extends AbstractSetup {

      /**
       * Download links
       *
       * @var array
       */
      protected $links;

      /**
       * Version selected
       *
       * @var string
       */
      protected $version;

      /**
       * Destination to unzip to
       *
       * @var string
       */
      protected $unzipped_destination;

      /**
       * Prompts to download a version
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return AbstractBinSetup
       */
      protected function promptDownload() {
         if(!empty($this->links)) {
            $version_numbers = array_keys($this->links);
            _echo('The following versions were found for download: ' . PHP_EOL . "\t"
                  . implode(PHP_EOL . "\t", $version_numbers));

            $io = trim(\IO::readline('Which version would you like to download? Input N to abort'));

            if(!$io) {
               $this->promptDownload();
            } elseif($io == 'n') {
               die('Aborting.');
            } elseif(!isset($this->links[$io])) {
               _echo('The version you selected is not available for download.');
               $this->promptDownload();
            } else {
               _echo('Contacting download server...');
               $this->version = $io;
               $this->download();

               return $this;
            }
         } else {
            die('Aborting.');
         }

         return $this;
      }

      /**
       * Downloads a version
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return AbstractBinSetup
       */
      protected function download() {
         $this->downloader = new Downloader($this->links[$this->version], $this->dest);

         if($this->downloader->download()) {
            _echo('Download successful. Setting up version ' . $this->version);
            sleep(1);
         } else {
            die('Download failed. Aborting setup.');
         }

         return $this;
      }
   }