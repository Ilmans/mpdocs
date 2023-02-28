<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
    	<meta charset="UTF-8">
        <style type="text/css">

            body {
                font-family: 'roboto', 'DejaVu Sans', sans-serif;
                overflow-x: hidden;
            }

            #page-wrap {
                width: 700px;
                margin: 0 auto;
            }

            div.lw-footer {
                position: fixed;
                width: 100%;
                border: 0px solid #888;
                overflow: hidden;
                padding: 0.1cm;
            }
            div.lw-footer {
                padding-top: 35px;
                padding-bottom: 10px;
                bottom: 10px;
                left: 20px;
                right: 20px;
                border-top-width: 1px;
                height: 0.5cm;
            }

            .lw-pdf-page-number {
              text-align: left;
              font-size: 12px;
            }
            
            .lw-pdf-page-number:before {
              content: counter(page);
            }

            .lw-break-before { page-break-before: always; }

         	/* unvisited link */
			a:link, a:visited, a:active {
			  text-decoration: none;
			  color: #000;
			}

			/* mouse over link */
			a:hover {
			  color: #1a82db;
			}

			ul {
				padding-left: 20px;
			}
			ul li {
				padding: 7px;
			}

			.float-left {
				float:left;
			}

			.float-left {
				float:right;
			}
			.align-right {
				text-align: right;
			}
			.align-left {
				text-align: right;
			}

			hr.custom-hr {
			    border: 0;
			    border-bottom: 1px dashed #ccc;
			    background: #999;
			}

			.lw-project-title {
				font-size: 40px;
				color: #f24f4f;
				text-transform: uppercase;
				box-sizing: content-box;
			}
        </style>
    </head>
    <body>

        <?php 

            function builHtmlTree($pages) {
                    
                $pageNevMarkup = '<ul type="disc">';
            
                foreach ($pages as $title => $page) 
                {   
                    $slug = array_get($page, 'slug');

                    if (__ifIsset($page['children'])) {

                        $contentTitle = array_get($page['content'], 'title');
                        
                        // this section contain children's 
                        $pageNevMarkup .= "<li><a href='#$slug'>$contentTitle</a>";
                        
                        $pageNevMarkup .= builHtmlTree($page['children']);

                        $pageNevMarkup .= "</li>";

                    } else {

                        $contentTitle = array_get($page['content'], 'title');
                            
                        $pageNevMarkup .= "<li ><a href='#$slug'>$contentTitle</a></li>";
                    }
                }

                $pageNevMarkup .= '</ul>';

                return  $pageNevMarkup;
            } 
            
            
            function buildSubDocument($items)
            {
                $string = "<div class='lw-break-before'>";

                foreach ($items as $i => $item) {

                    $title = array_get($item['content'], 'title');

                    $slug = array_get($item, 'slug');

                    $description = array_get($item['content'], 'description');
                    
                    $string .= "<div>";

                    $string .= "<h4 id='".$slug."'>".$title."</h4> <hr class='custom-hr'>";

                    $string .= "<p>".$description."</p>";

                    if (__ifIsset($item['children'])) {

                        $string .= buildSubDocument($item['children']);
                    }

                    $string .= "</div>";
                }

                $string .= "</div>";

                return $string;
            }
        ?>


	    <div id="page-wrap">

	    	
	            <!-- Project Info 1st page-->
	            <div style="">
	            	@if($logoFileExists)
	            	<img src="<?= $project['logo_url'] ?>" alt="<?= $project['name'] ?> ">
	            	@else
	            		<?= $project['name'] ?>
	            	@endif
	            </div>
	            <!-- /Project Info 1st page-->
            
			<div>
				<h1  class="lw-project-title" style="margin-top:200px;width: 100%;"><?= $project['name'] ?></h1>
			</div>

			<div>
				<h3 style="color:#585858;">Version : <?= $version['version'] ?></h3>
			</div>

            @if (!__isEmpty($articles))
            <!-- Table Of Content 2nd page -->
            <section class="lw-break-before">
				<h2>Contents : </h2>
                <?= builHtmlTree($articles) ?>
            </section>
            <!-- /Table Of Content 2nd page -->

            
            <article>

                @foreach($articles as $article)

                    <div class="lw-break-before">
                    
                        <h3 id="<?= $article['slug'] ?>"><?= $article['content']['title'] ?></h3>
                        
                        <p><?= $article['content']['description'] ?></p>
                        
                        @if(__ifIsset($article['children']))
    
                            <?= buildSubDocument($article['children']) ?>
                            
                        @endif
    
                    </div>

                @endforeach
            
            </article>
            @endif

            <div class="lw-footer">
              <div  class="lw-pdf-page-number"> / Page</div>
            </div>

        </div> 
    </body>
</html>