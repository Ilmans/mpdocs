<div>

    <!-- ARTICLES CATEGORIES SECTION -->
    <div class="lw-page-heading"> Contact Us </div>

	<div class="card">
		<div class="card-body">
			<form id="contact_us_form" method="post">
				<input type="hidden" name="_token" value="<?= csrf_token(); ?>">
				<div class="form-group">
				    <label for="fullname">Name</label>
				    <input   type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your name">
				</div>

				<div class="form-group">
				    <label for="email">Email</label>
				    <input   type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
				</div>

				<div class="form-group">
				    <label for="subject">Subject</label>
				    <input   type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject">
				</div>

				<div class="form-group">
				    <label for="message">Message</label>
				    <textarea   rows="5" class="form-control" id="message" name="message"></textarea>
				</div>

				<div class="text-center">
					<input class="btn btn-primary" id="contact_us_form_btn" type="submit" value="Submit">
				</div>
			</form>
		</div>
	</div>
</div>

@push('appScripts')
<script type="text/javascript">
	$(document).ready(function(){
		$("#contact_us_form_btn").on('click', function(e) {
			e.preventDefault();
    		var postData = {};
    		var formData = $('#contact_us_form').serializeArray();
        	$.each(formData, function() {
        		$( "#contact_us_form #"+this.name).removeClass('is-invalid')
        		postData[this.name] = this.value;
        	});

			$("#contact_us_form .invalid-feedback").remove();

    		$.ajax({
                url: '<?= route('public.contact.request.process') ?>',
                type: 'POST',
                data:  postData,
                dataType: 'JSON',
                success: function (data) {
                	var data = data;
                	if (data.reaction == 3 ) {
                		$.each(data.data.validation, function(key, value) {
                			$( "#contact_us_form #"+key).addClass('is-invalid')
                			$( "#contact_us_form #"+key).after("<div class='invalid-feedback'>"+ value +"</div>");
                  		});
                	}
                	if (data.reaction == 1) {
                		$( "<div class='alert alert-success alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>"+data.data.message+"</div>" ).prependTo( $( "#contact_us_form" ) );
                		$('#contact_us_form').trigger("reset");
                	}

                	if (data.reaction == 14 || data.reaction == 2) {
                		$( "<div class='alert alert-danger alert-dismissible'><button type='button' class='close' data-dismiss='alert'>&times;</button>"+data.data.message+"</div>" ).prependTo( $( "#contact_us_form" ) );
                	}

                	//auto close alert 
					window.setTimeout(function() {
					    $(".alert").fadeTo(500, 0).slideUp(500, function(){
					        $(this).remove(); 
					    });
					}, 4000);
                }
            });
		});
	});
</script>
@endpush