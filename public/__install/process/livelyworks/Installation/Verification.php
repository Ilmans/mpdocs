<?php
/**
 *  LivelyWorks EasyPHPInstall - PHP Application Installer
 *
 * @package    EasyPHPInstall - PHP Application Installer
 * @author     Vinod <livelyworks@gmail.com>
 * @copyright  2019 LivelyWorks. (http://livelyworks.net)
 * @link       http://livelyworks.net
 * @since      Version 1.0
 * @deprecated NA
 */
namespace LivelyWorks\Installation;
class Verification
{
    // problems container
    protected $problems = [];
    // config 
    protected $config = [
        'project_path' => '',
        'project_name' => '',
    ];
    // does every is ok
    protected $goodToGo = true;    
    // status container
    protected $statuses = [];
    // requirements structure
    protected $requirements = [
        'min_php_version' => null,
        'max_php_version' => null,
        'mysql_version' => null,                
        'extensions' => [                  
        ],
        'env_file' => null,
        'sql_file' => null,
        'is_writable' => [
        ]
    ];
    // basic styling for object
    protected $styleLines = "padding:5px;margin: 16px 0 30px;";    

    /**
     * Constructor.
     *
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        @ini_set('memory_limit', '-1');
        @ini_set('max_execution_time', '-1');
        
        $this->config = configItem();
        $this->requirements = \array_merge($this->requirements, $this->config['requirements'] ?: []);
    }

    /**
     * Verify the installation's basic requirements
     *
     * @return void
     *---------------------------------------------------------------- */
    public function verify()
    {
        if($this->requirements['min_php_version'] or $this->requirements['max_php_version']) {
            echo "<h5 class='lw-sub-head'> Your PHP version ".PHP_VERSION."</h5>";
            // check if php version fulfil minimum requirements
            if(!$this->requirements['max_php_version'] and version_compare(PHP_VERSION, $this->requirements['min_php_version']) >= 0) {
                echo "<div style='$this->styleLines'>✓ <strong style='color:green'>PHP</strong> Version is OK";
            }  // check if php version fulfil minimum & maximum requirements
            elseif($this->requirements['max_php_version'] and (version_compare(PHP_VERSION,                 $this->requirements['min_php_version']) >= 0) and (version_compare(PHP_VERSION,                 $this->requirements['max_php_version']) <= 0)) {
                echo "<div style='$this->styleLines'>✓ <strong style='color:green'>PHP</strong> version is OK";
            } else { // if not show invalid version error
                 echo "<div style='$this->styleLines color:red' >✗ Invalid PHP Version it should be greater than ".$this->requirements['min_php_version'];
                // show the issue if the version is greater than required
                if($this->requirements['max_php_version']) {
                    echo " and lower than ".$this->requirements['max_php_version'];
                }
                echo " </div>";
                // not so good
                $this->goodToGo = false;
                // add into problem container
                $this->problems['min_php_version'] = 'min_php_version';
            }
        }
        // check if required for extensions
        if(!empty($this->requirements['extensions'])) {
            // loop through the requirements
            foreach ($this->requirements['extensions'] as $key => $value) {
                // does the extension loaded
                if(extension_loaded($key)) {
                    // yes update the status
                    $this->statuses['extensions']['success'][] = $key;
                } else {
                    // no its not, still update the status
                    $this->statuses['extensions']['fail'][] = $key;
                }
            }
        }

        // check if required for extensions
        if(!empty($this->requirements['pecl_classes'])) {
            // loop through the requirements
            foreach ($this->requirements['pecl_classes'] as $key => $value) {
                // does the extension loaded
                if( class_exists( $key )) {
                    // yes update the status
                    $this->statuses['pecl_classes']['success'][] = $key;
                } else {
                    // no its not, still update the status
                    $this->statuses['pecl_classes']['fail'][] = $key;
                }
            }
        }

        // check if required for writable files
        if(!empty($this->requirements['is_writable'])) {
            // loop through the requirements
            foreach ($this->requirements['is_writable'] as $key => $value) {
                // does the file writable
                if(is_writable('./../'.$this->config['project_path'].'/'.$value)) {
                    // yes update the status
                    $this->statuses['writable']['success'][] = $value;
                } else {
                    // no its not, still update the status
                    $this->statuses['writable']['fail'][] = $value;
                }
            }
        }
        // ---------------------------- extensions --------------- 
        if(!empty($this->statuses['extensions']['success']) or !empty($this->statuses['extensions']['fail'])) {

            echo "<h5 class='lw-sub-head'> PHP Extension</h5>";
            // show the succeed extensions statuses
            if(!empty($this->statuses['extensions']['success'])) {
                echo '<span class="badge badge-success"> ✓ ' . implode('</span> <span class="badge badge-success"> ✓ ', $this->statuses['extensions']['success']) . "</span> <div> <br>These <strong style='color:green'>PHP extensions</strong> are Available</div> ";
            }
             // show the failed extensions statuses
            if(!empty($this->statuses['extensions']['fail'])) {
                // not good
                $this->goodToGo = false;
                echo ' <br><span class="badge badge-danger"> ✗ ' . implode('</span> <span class="badge badge-danger"> ✓ ', $this->statuses['extensions']['fail']) . "</span> <div> These <strong style='color:red'>PHP extensions</strong> are disabled. <br>";
                echo "<em class='text-muted'>Enable these extension using cPanel or php.ini or contact your hosting provider.</em></div>";
            } else {
                // if the failed status container is blank then seems 
                // that all the extensions are present
                echo '<div style="">All the required extensions are present</div>';
            }

        }

        // ---------------------------- PECL/Classes --------------- 
        if(!empty($this->statuses['pecl_classes']['success']) or !empty($this->statuses['pecl_classes']['fail'])) {

            echo "<h5 class='lw-sub-head'> PECL/Classes</h5>";
            // show the succeed extensions statuses
            if(!empty($this->statuses['pecl_classes']['success'])) {
                echo '<span class="badge badge-success"> ✓ ' . implode('</span> <span class="badge badge-success"> ✓ ', $this->statuses['pecl_classes']['success']) . "</span> <div> <br>These <strong style='color:green'> PECL/Classes</strong> are Available</div> ";
            }
             // show the failed extensions statuses
            if(!empty($this->statuses['pecl_classes']['fail'])) {
                // not good
                $this->goodToGo = false;
                echo ' <br><span class="badge badge-danger"> ✗ ' . implode('</span> <span class="badge badge-danger"> ✓ ', $this->statuses['pecl_classes']['fail']) . "</span> <div> These <strong style='color:red'> PECL/Classes</strong> not available. <br>";
            } else {
                // if the failed status container is blank then seems 
                // that all the extensions are present
                echo '<div style="">All the required PECL/Classes are present</div>';
            }

        }

        // ---------------------------- writable --------------- 
        if(!empty($this->statuses['writable']['success']) or !empty($this->statuses['writable']['fail'])) {
            echo "<h5 class='lw-sub-head'> Files and Folders Permissions</h5>";

            if(!empty($this->statuses['writable']['success'])) {
                // show the succeed writable items
                if(!empty($this->statuses['writable']['fail'])) {
                    echo "<div style='$this->styleLines color:green'>✓ " . implode('<br> ✓ ', $this->statuses['writable']['success']) . "<br> These <strong style='color:green'>Files/Directories</strong> are Writable <br>  </div>";
                } else {
                    // if the failed status container is blank then seems 
                    // that all the files are present/writable
                    echo "<div><span style='$this->styleLines'>✓ All the required <strong style='color:green'>Files/Directories</strong> are Writable <br></span> </div>";
                }
                
            }
             // show the failed writable items
            if(!empty($this->statuses['writable']['fail'])) {
                $this->goodToGo = false;
                echo "<div style='$this->styleLines color:red'>✗ " . implode('<br> ✗ ', $this->statuses['writable']['fail']) . "<br><br> These <strong style='color:red'>Files/Directories</strong> are Not available/writable. <br>";
                echo "<span style='color:#585858'>Give them writable permissions.</span> </div>";
            }

        }
        // check if good to go or not
        if($this->goodToGo) {
            echo '<div><span style="font-size:100px;" class="mdi mdi-check-decagram text-success"></span></div>';
            echo "<div style='".$this->styleLines."text-align:center;'><h1 class='text-primary'>Looks Good :)<br></h1>Basic requirements seems to be fine, You can proceed <br><br><a id='nextBtnDb' class='lw-button btn btn-primary btn-lg' href='#'>Next</a></div>";
        } else {
            echo '<div><span style="font-size:100px;" class="mdi mdi-alert-decagram text-danger"></span></div>';
                echo "<br><div style='text-align:center;'><h3 class='text-danger'>Ooooops .... Please fix highlighted issues in RED above.</h3></div>";
        }
    }

    /**
     * Verify the installation's user input and setup the database & env file
     *
     * @return void
     *---------------------------------------------------------------- */
    public function verifyUserInputs($inputData = [])
    {
        // env items container
        $envItems = [];
        // variables for database
        $dbHost = '';
        $dbPort   = '';
        $database   = '';
        $username   = '';
        $password   = '';
        // check there are items for env
        if(!empty($this->config['envItems'])) {
            // loop through the items
            foreach ($this->config['envItems'] as $envItemKey => $envItemValue) {
                // get item to identify the inputs
                $md5Item = md5($envItemKey);
                // clean the data & assign
                $inputItem = cleanData($inputData[$md5Item]);
                // if the required item missing show error
                if(($envItemValue['required'] === true) and (!$inputItem )) {
                     die('<div><span style="font-size:100px;" class="mdi mdi-alert-decagram text-danger"></span></div>'."<div style='$this->styleLines'>✗ <strong style='color:red'>$envItemKey is required </strong></div>");
                }
                // identify the database variables 
                if($envItemValue['item_type'] == 'database_host') {
                   $dbHost =  $inputItem;
                } elseif($envItemValue['item_type'] == 'database_port') {
                   $dbPort =  $inputItem;
                } elseif($envItemValue['item_type'] == 'database_name') {
                   $database =  $inputItem;
                } elseif($envItemValue['item_type'] == 'database_username') {
                   $username =  $inputItem;
                } elseif($envItemValue['item_type'] == 'database_password') {
                   $password =  $inputItem;
                }
                // feed the env items container
                $envItems[$envItemKey] = $inputItem;
            }
        }
        // any page content to print
        $onPageContent = '';
        // any page modal content to print
        $pageContent = '';
        // set the database connection status
        $isDatabaseConnectionSucceed = false;

        // Create connection
        $conn = new \mysqli($dbHost, $username, $password, $database);
        // Check connection
        if ($conn->connect_error) { // on error show it
            $this->goodToGo = false;
            $pageContent .= '<div><span style="font-size:100px;" class="mdi mdi-alert-decagram text-danger"></span></div>';
            $pageContent .= "<div style='$this->styleLines'>✗ <strong style='color:red'>Database Connection Failed.</strong><br> ";
            $pageContent .= "<span style='color:#585858'>".$conn->connect_error."</span> </div>";
        } else { // not not an error proceed
            // set the page content
             $pageContent .= '<div><span style="font-size:100px;" class="mdi mdi-check-decagram text-success"></span></div>';
            $pageContent .= "<div style='$this->styleLines'><span style='$this->styleLines'>✓ <strong style='color:green'>Database Connection Succeed</strong></span> </div>";
            // check if the provided the env file
            if($this->config['env_file'] and !empty($envItems)) {
                // update the provided env file
                if(updateEnvValue($envItems, './../'.$this->config['env_file'])) {
                    $pageContent .= "<div style='$this->styleLines'><span style='$this->styleLines'>✓ <strong style='color:green'> ENV DB Configuration Updated</strong></span> </div>";
                } else { // if failed to update env file
                    die('<div><span style="font-size:100px;" class="mdi mdi-alert-decagram text-danger"></span></div>'."<div style='$this->styleLines'>✗ <strong style='color:red'>Problem updating ENV configurations </strong></div>");
                };
            }
            // check if the provided the sql file to feed base database
            if($this->config['sql_file']) {
                // prepare query if the file found
                $query = '';
                if(!\file_exists('./../'.$this->config['sql_file'])) {
                     die('<div><span style="font-size:100px;" class="mdi mdi-alert-decagram text-danger"></span></div>'."<div style='$this->styleLines'>✗ <strong style='color:red'>".$this->config['sql_file']." file not found </strong></div>");
                }
                // get file contents
                $sqlScript = file('./../'.$this->config['sql_file']);
                // loop through the file contents
                foreach ($sqlScript as $line)	{                    
                    $startWith = substr(trim($line), 0 ,2);
                    $endWith = substr(trim($line), -1 ,1);
                    
                    if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                        continue;
                    }
                        
                    $query = $query . $line;
                    if ($endWith == ';') {
                        // run the queries or show the error if happen
                        mysqli_query($conn,$query) or die('<div><span style="font-size:100px;" class="mdi mdi-alert-decagram text-danger"></span></div>'."<div style='$this->styleLines'>✗ <strong style='color:red'>Problem in executing the SQL query </strong><br>" . mysqli_error($conn). '</div>');
                        $query= '';		
                    }
                }
                // show the message 
                $pageContent .= "<div style='$this->styleLines'><span style='$this->styleLines'>✓ <strong style='color:green'>Database Prepared</strong></span> </div>";
            }
            // it seems connection was successful
            $isDatabaseConnectionSucceed = true;
            // close the connection
            // mysqli_close($conn);
            // success message
            $pageContent .= "<div style=''><a class='lw-main-btn btn btn-primary btn-lg' href='".$this->config['success_redirect']."'>Congrats everything seems to be working</a></div>";
            // assign page contents
            $onPageContent = $pageContent;

            $conn->close();
        }
        // get back to the request with data
        return [
            'onPage' => $onPageContent,
            'page' => $pageContent,
            'isDatabaseConnectionSucceed' => $isDatabaseConnectionSucceed
        ];
    }
}
