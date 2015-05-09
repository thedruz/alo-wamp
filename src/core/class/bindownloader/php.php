<?php

   namespace BinDownloader;

   use \Format;
   use \IO;
   use \Downloader;

   class PHP extends \Setup\PHP {

      protected $installed_vers;

      /**
       * @return PHP
       */
      protected function getInstalledVers() {
         $dir = DIR_PHP;

         if (!file_exists($dir)) {
            die('PHP directory not found');
         } else {
            $scan = scandir($dir);
            Format::formatScandir($scan);
            $this->installed_vers = $scan;
         }

         return $this;
      }

      /**
       * @return PHP
       */
      protected function filterLinks() {
         foreach ($this->installed_vers as $v) {
            if (isset($this->links[$v])) {
               unset($this->links[$v]);
            }
         }

         return $this;
      }

      protected function updateSettings() {
         $io = IO::readline('Would you like to set this version as the default PHP interpreter for future installations? [Y/N]');

         if ($io == 'y') {
            parent::updateSettings();
         }

         return $this;
      }

      protected function promptDownload() {
         $this->getInstalledVers()->filterLinks();
         if (!empty($this->links)) {
            $version_numbers = array_keys($this->links);
            _('The following versions were found for download (versions already installed are not included): ' . PHP_EOL . "\t"
               . implode(PHP_EOL . "\t", $version_numbers));

            $io = trim(IO::readline('Which version would you like to download? Input N to abort'));

            if (!$io) {
               $this->promptDownload();
            } elseif ($io == 'n') {
               die('Aborting.');
            } elseif (!isset($this->links[$io])) {
               _('The version you selected is not available for download.');
               $this->promptDownload();
            } else {
               $this->version = $io;
               $this->download();

               return $this;
            }
         } else {
            die('Aborting.');
         }

         return $this;
      }
   }