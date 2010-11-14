<?php
/************************************************************************
 * This file is part of phpIPN.                                         *
 *                                                                      *
 * phpIPN is free software: you can redistribute it and/or modify       *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation, either version 3 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * phpIPN is distributed in the hope that it will be useful,            *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with phpIPN.  If not, see <http://www.gnu.org/licenses/>.      *
 ************************************************************************/
//require_once("../../../../../include/includes.php");

/**
 * Planet Angel Instant Payment Notification System Administration Panel.
 * E-mail template administration
 *
 * @author Dafydd James <dafydd@cantab.net>
 */

/**
 * 
 * generate database reports
 */
class Templates extends Controller {

    const TEMPLATES_DIRECTORY = "../templates/";
    private $templatePath = '';
    private $templateFiles = array();

    function __construct() {
        parent::Controller();
        $this->templatePath = self::TEMPLATES_DIRECTORY;
        $this->load->helper(array('file', 'form', 'url', 'directory'));
    }
    
    /**
     * index() function - called when page loads.
     */
    function index() {
        $map = $this->getDirectoryMap($this->templatePath);
        $templateContents = $this->getTemplateContentsMap($map);
        $data = array('title'           => 'Template Administration', 
                      'directoryMap'    => $map,
                      'templateContents'=> $templateContents);
                      
        $this->load->view('header', $data);
        $this->load->view('templatesView', $data);
        $this->load->view('footer', $data);
    }
    
    /**
     * Template edit screen.
     */
    function edit($filename) {
        $templateContents = read_file($this->templatePath . $filename);

        $textareaParameters = array('name'  => 'contents',
                                    'value' => $templateContents,
                                    'cols' => 140,
                                    'rows'  => 60);
        
        $data = array('title'              => 'Edit Template',
                      'filename'           => $filename,
                      'templateContents'   => $templateContents,
                      'textareaParameters' => $textareaParameters);
                      
        $this->load->view('header', $data);
        $this->load->view('templateEditView', $data);
        $this->load->view('footer', $data);
        
    }

    /**
     * Template save action.
     */
    function save($filename) {
        if($this->input->post('contents')) {
            $contents = $this->input->post('contents');
            $originalContents = $this->getTemplateContents($filename);
            if($contents !== $originalContents) {
                $fullFilename = $this->templatePath . $filename;
                $status = write_file($fullFilename, $contents);
                if(true === $status) {
                    error_log("Wrote file $fullFilename with status $status");
                } else {
                    error_log("Failed to write file $fullFilename");
                }
            } else {
                echo "Same";
            }
        }

        $data = array('title' => 'Template Save Screen',
                      'filename' => $filename);

        $this->load->view('header', $data);
        $this->load->view('templateSaveView', $data);
        $this->load->view('footer', $data);
    }
    
    /**
     * retrieve contents of all files.
     */
    function getTemplateContents($filename) {
        $contents = read_file($this->templatePath . $filename);
        return $contents;
    }
    /**
     * retrieve contents of all files.
     */
    function getTemplateContentsMap($directoryMap) {
        $contentsMap = array();
        foreach($directoryMap as $filename) {
            $contents = read_file($this->templatePath . $filename);
            $contentsMap[$filename] = $contents;
        }
        return $contentsMap;
    }
    
    /**
     * Return directory map, CodeIgniter-style.
     */
    function getDirectoryMap($directory) {
        $map = directory_map($directory);
        $templates = array();
        foreach($map as $file) {
            if(0 === strpos($file, "confirmation_", 0) && "txt" === $this->getFileExtension($file)) {
                $templates[] = $file;
            }
        }
        return $templates;
    }
    
    function getFileExtension($filename) {
        $ext = substr($filename, strrpos($filename, '.') + 1);
        return $ext;
    } 
}
