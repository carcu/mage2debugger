<?php
/**
 * Showlogsnav.
 *
 * @copyright Copyright © 2017 SalesIgniter. All rights reserved
 * @author    cristian@itwebexperts.com
 */

namespace SalesIgniter\Debugger\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;

class Showlogsnav extends Template
{
    /**
     * @var string
     */
    protected $_template = 'showlogsnav.phtml';

    protected $_originalDir;
    protected $_originalDirUri;

    public function makeULLI($array)
    {
        $return = "<ul>\n";

        if (is_array($array) && count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v) && count($v) > 0) {
                    $return .= "\t<li>".$k.$this->makeULLI($v)."</li>\n";
                } elseif (!is_array($v)) {
                    $return .= "\t<li>".$v."</li>\n";
                }
            }
        } else {
        }

        $return .= '</ul>';

        return $return;
    }

    public function getTree()
    {
        $mediaDirectory = \Magento\Framework\App\ObjectManager::getInstance()->get(Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA);
        $dir = $mediaDirectory->getAbsolutePath().'sidebugger1/logs/';
        $this->_originalDir = $dir;
        /** @var \Magento\Framework\App\ObjectManager $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $om->get(Filesystem::class);
        $driverFile = $om->get(\Magento\Framework\Filesystem\Driver\File::class);
        $storeManager = $om->get(StoreManagerInterface::class);
        $baseUrl = $storeManager->getStore()->getBaseUrl().$filesystem->getUri(DirectoryList::MEDIA).'/sidebugger1/logs/';
        $this->_originalDirUri = $baseUrl;
        try {
            $treeArray = $this->getTreeUl($driverFile, $dir);
            //$treeArray = $this->getTreeUlOld($dir);

            return $this->makeULLI($treeArray);
        } catch (\Exception $exception) {
            return $exception->getMessage().''.$dir.'no files';
        }
    }

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param                                           $directoryPath
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getTreeUl($driverFile, $directoryPath)
    {
        $return = array();
        if ($driverFile->isExists($directoryPath)) {
            $files = $driverFile->readDirectory($directoryPath);
            foreach ($files as $file) {
                /*foreach ($excludePatterns as $pattern) {
                    if (preg_match($pattern, $file)) {
                        continue 2;
                    }
                }*/
                if ($driverFile->isFile($file)) {
                    $currentDir = str_replace($this->_originalDir, '', $file);

                    $url = $this->_originalDirUri.$currentDir;
                    $link = '<a href="'.$url.'">'.basename($file).'</a>';
                    $return[] = $link;
                } else {
                    $origFile = $file;
                    $link = str_replace($this->_originalDir, '', $file);
                    $return[$link] = $this->getTreeUl($driverFile, $origFile);
                }
            }
        }

        return $return;
    }
}
