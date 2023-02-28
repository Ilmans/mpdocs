
<div class="lw-form-append-btns">
    <button type="button" class="lw-btn btn btn-primary btn-sm lw-btn-file">
        <i class="fa fa-upload"></i> <?= __tr('Browse to Upload') ?>&nbsp;&nbsp;
        
        <span id="lw-spinner-widget" class="lw-spinner-widget">
        <i class="fa fa-sync-alt fa-spin"></i>
        </span>

        <input id="lwFileupload" class="hide-till-load" ng-click="upload()" type="file" name="upload-file" multiple>
    </button>
    <button type="button" ng-click="showUploadedMediaDialog()" title="<?= __tr('Uploaded Files') ?>" class="lw-btn btn btn-light btn-sm lw-btn-file" ><?= __tr('Uploaded Files') ?></button>
</div>