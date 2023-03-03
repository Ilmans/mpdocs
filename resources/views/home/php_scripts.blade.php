<?php 
  
    function buildSidebarContents($pages, $projectSlug, $versionSlug, $language, $articleSlug = null) {
        // Open unorder list
        $pageNevMarkup = '<ul>';

        foreach ($pages as $page) {
            // Create Route for doc view
            $route = route('doc.view', [
                'projectSlug' => $projectSlug,
                'versionSlug' => $versionSlug,
                'articleSlug' => $page['parentSlug']
            ]).'?lang='.$language;

            // Get Content Title and Slug
            $contentTitle = array_get($page, 'title');
            $contentSlug = array_get($page, 'slug');

            // Check if current page is parent
            if ($page['languages__id'] == $language) {
                if ($page['isParent']) {
                    $pageNevMarkup .= "<li class='lw-article-link lw-menu-parent-item lw-menu-parent-".$page['parentSlug']."'><a class='".(($articleSlug == $page['parentSlug']) ? ' lw-active-article' : '')."' href='$route'>$contentTitle</a><div class='lw-index-loader-spinner'></div><ul></ul>";
                } else {
                    $pageNevMarkup .= "<li class='lw-article-link'><a href='$route#$contentSlug'>$contentTitle</a>";
                }
            }

            if (isset($page['sub_articles'])) {
                // $pageNevMarkup .= buildSidebarContents($page['sub_articles'], $projectSlug, $versionSlug, $language);
            }

            $pageNevMarkup .= '</li>';
        }
        // Close unordered list
        $pageNevMarkup .= '</ul>';
        // Return page markup
        return $pageNevMarkup;
	}
	
	//Note : Level has been replaced with depth
    function buildArticleContent($items, $language, $level = 0)
    {
        
        // Open unordered list
        $pageNevMarkup = '';

        foreach ($items as $page) {

            // Get Content Title and Slug
            $contentTitle = array_get($page, 'title');
            $contentLang = array_get($page, 'slug');
            $contentDescription = array_get($page, 'description');
            
          
            // Check if current page is parent
            if ($page['languages__id'] == $language) {

                if ($page['isParent']) {
                 
                    $pageNevMarkup .= "<h3 class='lw-document-heading mb-3' id='$contentLang'>$contentTitle</h3>";
                    $pageNevMarkup .= "<div class='lw-article-description mb-4'>$contentDescription</div>";
                } else {
                    $pageNevMarkup .= "<div class='lw-document-child-content'>";
                    $pageNevMarkup .= "<div class='lw-document-sub-heading' data-child-level='".array_get($page, 'depth')."' id='$contentLang'>$contentTitle </div>";
                    $pageNevMarkup .= "<p>$contentDescription</p>";
                    $pageNevMarkup .= "</div>";

                }
            }

            if (isset($page['sub_articles'])) {
                $level++;
                $pageNevMarkup .= buildArticleContent($page['sub_articles'], $language, $level);
            }
        }
        // Close unorder list
        $pageNevMarkup .= '';
        // Return page markup
        return $pageNevMarkup;
    }