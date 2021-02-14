<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class FilterUsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $status_plan = config('app.status_plan');
        return [
            'columnTag' => 'required|boolean',
            'search' => 'string|min:3',
            'tag' => 'string',
            'plan' => 'string',
            'statusPlan' => 'in:Active,Inactive,Trial,Expired,Cancelled,Failed,VerifyRequired',
            'userType' => 'in:owner,subuser,superadmin',
            'relatedUsers.between.from' => 'integer',
            'relatedUsers.between.to' => 'integer',
            'relatedUsers.less' => 'integer',
            'relatedUsers.greater' => 'integer',
            'relatedUsers.equal' => 'integer',
            'location.city' => 'string',
            'location.country' => 'string',
            'location.state' => 'string',
            'lastActivity.between.from' => 'date',
            'lastActivity.between.to' => 'date',
            'lastActivity.less' => 'date',
            'lastActivity.greater' => 'date',
            'lastActivity.equal' => 'date',
            'createdAt.between.from' => 'date',
            'createdAt.between.to' => 'date',
            'createdAt.less' => 'date',
            'createdAt.greater' => 'date',
            'createdAt.equal' => 'date',
            'contactSize.between.from' => 'integer',
            'contactSize.between.to' => 'integer',
            'contactSize.less' => 'integer',
            'contactSize.greater' => 'integer',
            'contactSize.equal' => 'integer',
            'views.between.from' => 'integer',
            'views.between.to' => 'integer',
            'views.less' => 'integer',
            'views.greater' => 'integer',
            'views.equal' => 'integer',
            'bandwidthUsage.between.from' => 'numeric',
            'bandwidthUsage.between.to' => 'numeric',
            'bandwidthUsage.less' => 'numeric',
            'bandwidthUsage.greater' => 'numeric',
            'bandwidthUsage.equal' => 'numeric',
            'pagination.offset' => 'required|integer',
            'pagination.limit' => 'required|integer',
            'byDate' => 'in:asc,desc',
            'byName' => 'in:asc,desc',
            'byNoRelatedUsers' => 'in:asc,desc',
            'byContactSize' => 'in:asc,desc',
            'byViewCount' => 'in:asc,desc',
            'byBandwidthUsage' => 'in:asc,desc',
            'byStorageSize' => 'in:asc,desc',
            'byNoOfVideos' => 'in:asc,desc',
            'byServiceCost' => 'in:asc,desc',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
