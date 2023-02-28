<?php

    /**
     * Custom validation rules for check unique email address -
     * for user.
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('unique_email', function ($attribute, $value, $parameters) {
        $email = strtolower($value);
        $userCount = App\Yantrana\Components\User\Models\User::where('email', $email)
                        ->get()
                        ->count();

        // Check for user exist with given email
        if ($userCount > 0) {
            return false;
        }

        $newEmailRequestCount = App\Yantrana\Components\User\Models\EmailChangeRequest::where('new_email', $email)
                                    ->count();
        // Check for new email request exist with given email
        if ($newEmailRequestCount > 0) {
            return false;
        }

        return true;
    });

    /**
     * verify number format -
     * for user.
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('unique_article_title', function ($attribute, $value, $parameters) {
        $docVersionId = array_get($parameters, '0');
        $articleContentId = array_get($parameters, '1');
        $articleId = array_get($parameters, '2');
        
        $title = strtolower($value);

        $articleQuery = App\Yantrana\Components\Article\Models\ArticleModel::leftJoin('article_contents', 'articles._id', '=', 'article_contents.articles__id')
        ->where(function($query) use($docVersionId, $title) {
            $query->where(['articles.doc_versions__id' => $docVersionId, 'article_contents.title' => $title])
                    ->orWhere('articles.slug', slugIt($title))
                    ->where('articles.doc_versions__id', $docVersionId);
        });

        if (!__isEmpty($articleContentId)) {
            $articleQuery->whereNotIn('article_contents._id', [$articleContentId]);
        }

        if (!__isEmpty($articleId)) {
            $articleQuery->whereNotIn('articles._id', [$articleId]);
        }
        
        $articleCount = $articleQuery->count();
        
        if ($articleCount > 0) {
            return false;
        }

        return true;
    });

    /**
     * Custom validation rules for check unique email address -
     * for user.
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('unique_in_email_change_request', function ($attribute, $value, $parameters) {
        $email = strtolower($value);
        
        $newEmailRequestCount = App\Yantrana\Components\User\Models\EmailChangeRequest::where('new_email', $email)->count();
        // Check for new email request exist with given email
        if ($newEmailRequestCount > 0) {
            return false;
        }

        return true;
    });


    /**
     * Custom validation rules for check verify currency format -
     *
     * {__currencySymbol__}{__amount__} {__currencyCode__} this is format contains
     *
     * take reference from - config('__settings.items.currency_format.default')
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('verify_format', function ($attribute, $value, $parameters) {
        $condition = false;

        if (str_contains($value, '{__amount__}')) {
            $condition = true;
        }
        
        // Check if currency symbol exist only one time in string
        if (substr_count($value, '{__currencySymbol__}') > 1
            or substr_count($value, '{__amount__}') > 1
            or substr_count($value, '{__currencyCode__}') > 1) {
            $condition = false;
        }

        return $condition;
    });

     /**
     * verify number format -
     * for user.
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('verify_number_format', function ($attribute, $value, $parameters) {
      	$condition = false;

        if ($value >= 1 && $value <= 10) {
            $condition = true;
        }

        return $condition;
    });

    /**
     * verify number format -
     * for user.
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('different_array', function ($attribute, $value, $parameters) {
        $inputData = request()->all();        

        $productSkuIds = [];
        foreach ($inputData['optionLabels'] as $optionLabelKey => $optionLabel) {
            if (!in_array($optionLabel['product_id'], $productSkuIds)) {
                $productSkuIds[] = $optionLabel['product_id'];
            } else {
                return false;
            }
        }

        return true;
    });

    /**
     * Slug Validation for string
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('slug', function($attribute, $value, $parameters, $validator) {
        return preg_match('/^[A-Za-z0-9_.-]+(?:-[A-Za-z0-9_.-]+)*$/', $value);
    });

    /**
     * verify recaptcha -
     * for user.
     *
     * @return bool
     *---------------------------------------------------------------- */
    Validator::extend('recaptcha', 'App\\Yantrana\\Services\\ReCaptcha\\ReCaptcha@validate');