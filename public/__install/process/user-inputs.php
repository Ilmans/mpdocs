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
// Prevent if the installation successful
if(!file_exists('./../../install.php')) {
    header("HTTP/1.0 404 Not Found");
    die();
}
// show the errors
error_reporting(E_ALL & ~E_WARNING);
ini_set("display_errors", 1);  
require 'vendor/autoload.php';
// env items
$envItems = envItems();
if(!$_POST['requirements_fulfilled'] and ($_POST['requirements_fulfilled'] != true)) {
    header("Location: index.php");
}
?>
<form id="dbInfoForm" method="post">  
<h3 class='lw-sub-head'> Database Setup & Preparation</h3>
<?php foreach ($envItems as $envItemKey => $envItemConf):
    $md5EnvItemKey = md5($envItemKey);
?>
    <div class="form-group">
        <label for="<?= $md5EnvItemKey ?>"><?= $envItemKey ?></label>
        <input type="<?= $envItemConf['type'] ?>" class="form-control" <?= $envItemConf['required'] ? 'required' : '' ?> value="<?= $envItemConf['value'] ?>" name="<?= $md5EnvItemKey ?>" placeholder="<?= $envItemConf['placeholder'] ?>">
        <?php if($envItemConf['note']): ?>
        <small class="form-text text-muted"><?= $envItemConf['note'] ?></small>
        <?php endif; ?>
    </div>
<?php endforeach; ?>      
    <input type="hidden" name="requirements_fulfilled", value="true">
    <div class="form-group text-center">
    <input class="lw-main-btn btn btn-primary btn-lg" id="dbInfoSubmit"  type="submit" name="submit" value="Connect & Prepare Database">  
     </div>
    </form>
 <div id="otherDynamicPageContent">
      </div>
<div class="modal fade" id="dynamicPageContentModal" tabindex="-1" role="dialog" aria-labelledby="dynamicPageContentModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dynamicPageContentModalTitle">Alert</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="dynamicPageContent">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
 