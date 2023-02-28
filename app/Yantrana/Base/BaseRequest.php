<?php 
namespace App\Yantrana\Base;

use Illuminate\Http\Request;
use App\Yantrana\__Laraware\Core\CoreRequest;
use Illuminate\Validation\Factory as ValidatorFactory;

use Auth;

abstract class BaseRequest extends CoreRequest
{

    /**
    * Modify form request response
    *
    * @param  $errors
    *
    * @return array
    *------------------------------------------------------------------------ */
    public function response(array $errors)
    {

    //return \Response::json($errors);

        if (Request::ajax()) {
            return __apiResponse([
                    'validation'  => $errors,
            'message'     => __tr("Ooops..., looks like something went wrong!")
        ], 3);
        }

        return $this->redirector->to($this->getRedirectUrl())
                            ->withInput($this->except($this->dontFlash))
                            ->withErrors($errors);
    }

    /**
     * Modify validator.
     *
     * @param  $factory
     *
     * @return array
     *------------------------------------------------------------------------ */
    public function validator(ValidatorFactory $factory)
    {
        $result = parent::validator($factory);

        if (
            $this->isMethod('post')
            and Auth::check()
            and (canAccess('demo_authority') !== true)) {
            if ($result->passes()) {
                exit(__processResponse(['reaction_code' => 1, 'message' => null], [
                    1 => __tr('Saving functionality is disabled in this demo...'),
                ], ['__useNativeJsonEncode' => true]));
            }
        }

        return $result;
    }
}
