<!-- text field template -->
<script type="text/ng-template" id="lw-form-text.ngtemplate" >
    <div class="form-group lw-remove-transclude-tag">
        <label ng-show="formField[fieldFor]['label']" for="[[ formField[fieldFor]['fieldFor'] ]]" class="[[ formField[fieldFor]['labelClass'] ]]">
            [[ formField[fieldFor]['label'] ]]
        </label>
            <ng-transclude class="[[ formField[fieldFor]['inputClass'] ]]"></ng-transclude>
        <div ng-messages="lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$error']"
         ng-class="{ 'lw-dirty' : ( lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$dirty'] || lwFormData.formCtrl.submitted ) }" ng-include src="'lw-form-validation-template.html'">
        </div>
    </div>
</script>
<!-- /text field template -->

<script type="text/ng-template" id="lw-form-selectize.ngtemplate">
    <div class="form-group">
        <label ng-show="formField[fieldFor]['label']" for="[[ formField[fieldFor]['fieldFor'] ]]" class="[[ formField[fieldFor]['labelClass'] ]]">
            [[ formField[fieldFor]['label'] ]]
        </label>
       <ng-transclude></ng-transclude>
        <!-- include field validation template -->
        <div ng-messages="lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$error']"
         ng-class="{ 'lw-dirty' : ( lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$dirty'] || lwFormData.formCtrl.submitted ) }" ng-include src="'lw-form-validation-template.html'">
        </div>
    </div>
</script>

<!-- checkbox field template -->
<script type="text/ng-template" id="lw-form-checkbox-field.ngtemplate">
	<div class="form-group">
		<ng-transclude class="[[ formField[fieldFor]['inputClass'] ]]"></ng-transclude>
		<label ng-show="formField[fieldFor]['label']" for="[[ formField[fieldFor]['fieldFor'] ]]" class="[[ formField[fieldFor]['labelClass'] ]]">
			<span ng-show="formField[fieldFor]['offLabel']" ng-bind="lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ].$modelValue ? formField[fieldFor]['label'] : formField[fieldFor]['offLabel'] "></span>
			<span ng-hide="formField[fieldFor]['offLabel']" ng-bind="formField[fieldFor]['label']"></span>
		</label>
		<div ng-messages="lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$error']"
		ng-class="{ 'lw-dirty' : ( lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$dirty'] || lwFormData.formCtrl.submitted ) }" ng-include src="'lw-form-validation-template.html'">
		</div>
	</div>
</script>
<!-- /checkbox field template -->

<!-- select all de-select all checkbox field template -->
<script type="text/ng-template" id="lw-select-all-checkbox-field.ngtemplate">
    <input type="checkbox" ng-model="master" ng-change="masterChange()">
</script>
<!-- /select all de-select all checkbox field template -->

<!-- radio button field template -->
<script type="text/ng-template" id="lw-form-radio-field.ngtemplate">
    <div class="form-group">
		<label ng-show="formField[fieldFor]['label']">[[ formField[fieldFor]['label'] ]]</label><br>
        <ng-transclude class="[[ formField[fieldFor]['inputClass'] ]]"></ng-transclude>
        <div ng-messages="lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$error']"
         ng-class="{ 'lw-dirty' : ( lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$dirty'] || lwFormData.formCtrl.submitted ) }" ng-include src="'lw-form-validation-template.html'">
        </div>
    </div>
</script>
<!-- /radio button field template -->

<!-- form field validation errors template help-block-->
<script type="text/ng-template" 
		id="lw-form-validation-template.html">
        <span class="text-danger lw-error-message" ng-message="required">
          [[ formField[fieldFor]['vLabel'] ? getValidationMsg('required', formField[fieldFor]['vLabel']) : getValidationMsg('required', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger lw-error-message" ng-message="email">
            [[ formField[fieldFor]['vLabel'] ? getValidationMsg('email', formField[fieldFor]['vLabel']) : getValidationMsg('email', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger help-block lw-error-message" ng-message="number">
          [[ formField[fieldFor]['vLabel'] ? getValidationMsg('numeric', formField[fieldFor]['vLabel']) : getValidationMsg('numeric', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger lw-error-message" ng-message="minlength">
          [[ formField[fieldFor]['vLabel'] ? getSizeValidationMsg('min', formField[fieldFor]['vLabel']) : getSizeValidationMsg('min', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger lw-error-message" ng-message="maxlength">
          [[ formField[fieldFor]['vLabel'] ? getSizeValidationMsg('max', formField[fieldFor]['vLabel']) : getSizeValidationMsg('max', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger lw-error-message" ng-message="min">
          [[ formField[fieldFor]['vLabel'] ? getMinMaxValidationMsg('min', formField[fieldFor]['vLabel']) : getMinMaxValidationMsg('min', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger lw-error-message" ng-message="max">
          [[ formField[fieldFor]['vLabel'] ? getMinMaxValidationMsg('max', formField[fieldFor]['vLabel']) :  getMinMaxValidationMsg('max', formField[fieldFor]['label']) ]]
        </span>
        <span class="text-danger lw-error-message" ng-message="unique">
          Value name should be unique for the option.
        </span>
        <span class="text-danger lw-error-message" ng-message="server">
         [[ lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$error']['server'] ]]
        </span>
</script>
<!-- /form field validation errors template -->
<div style="filter: alpha(opacity=0);opacity:0;text-indent: -5000px;position: absolute;display: block;top: -5000px;">livelyworksProductId:<?= configItem('entity_ownership_id') ?>-<?= configItem('PRODUCT_VER') ?></div>


<!-- Selectize template -->
<script type="text/ng-template" id="lw-form-selectize.ngtemplate">
    <div class="form-group">
        <label for="[[ formField[fieldFor]['fieldFor'] ]]" class="[[ formField[fieldFor]['labelClass'] ]]">
            [[ formField[fieldFor]['label'] ]]
        </label>
       <ng-transclude></ng-transclude>
        <!-- include field validation template -->
        <div ng-messages="lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$error']"
         ng-class="{ 'lw-dirty' : ( lwFormData.formCtrl[ formField[fieldFor]['fieldFor'] ]['$dirty'] || lwFormData.formCtrl.submitted ) }" ng-include src="'lw-form-validation-template.html'">
        </div>
    </div>
</script>
<!-- /Selectize template -->

<!-- select form field template -->
<script type="text/_template" id="lwFlatpickrAction">

    <div class="flatpickr-action" tabindex="-1">
        <% if(__tData.pickerOptions.clearButton) { %>
            <button type="button" class="btn btn-warning btn-sm flatpickr-clear">Clear</button>
        <% } %>

        <% if(__tData.pickerOptions.closeButton) { %>
            <button type="button" class="btn btn-defualt btn-sm flatpickr-close">Close</button>
        <% } %>

    </div>
</script>
<!-- /select form field template -->

<!-- Document Editor -->
<script type="text/ng-template" id="lw-document-editor.ngTemplate">
    
    <div>

        <div class="document-editor">

            <div class="document-editor__toolbar"></div>
            
            <div class="document-editor__editable-container">
                
                <div class="document-editor__editable">

                    <p><ng-transclude></ng-transclude></p>

                </div>

            </div>

        </div>

    </div>
     
</script>
<!-- /Document Editor -->


