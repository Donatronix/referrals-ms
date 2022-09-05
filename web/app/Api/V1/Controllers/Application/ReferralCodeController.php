<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Exceptions\ReferralCodeLimitException;
use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use App\Services\ReferralService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Referral code Controller
 *
 * @package App\Api\V1\Controllers\Application
 */
class ReferralCodeController extends Controller
{
    /**
     * Get referral codes and links
     *
     * @OA\Get(
     *     path="/referral-codes",
     *     description="Get all user's referral codes and links",
     *     tags={"Application | Referral Codes"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="application_id",
     *         description="Application ID",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *              default="app.sumra.wallet"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="The list of referral codes has been displayed successfully"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     )
     * )
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            $codes = ReferralCode::byOwner()
                ->when($request->has('application_id'), function ($q) {
                    return $q->byApplication();
                })
                ->get();

            return response()->jsonApi([
                'title' => 'List referral codes',
                'message' => 'list referral codes was received successfully',
                'data' => $codes,
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'List referral codes',
                'message' => 'Error reading list of referral codes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     *  Create link and code for an existing user
     *
     * @OA\Post(
     *     path="/referral-codes",
     *     summary="Create link and code for an existing user",
     *     description="Create link and code for an existing user",
     *     tags={"Application | Referral Codes"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Is Default referral code / link. Accept 1, 0, true, false",
     *                 example="false"
     *             ),
     *             @OA\Property(
     *                 property="note",
     *                 type="string",
     *                 description="Note about referral code",
     *                 example="Code for facebook"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success create link and code",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws Exception
     */
    public function store(Request $request): mixed
    {
        // Validate input data
        $validator = Validator::make($request->all(), ReferralCode::$rules);

        if ($validator->fails()) {
            return response()->jsonApi([
                'title' => 'Referral code generate',
                'message' => $validator->errors()->first()
            ], 422);
        }

        // @ToDo Temporary set application ID
        $request->merge(['application_id' => 'default_app']);

        // Try to create new code with link
        try {
            // Check user in the referral program
            $user = ReferralService::getUser(Auth::user()->getAuthIdentifier());

            // Create new code
            $code = ReferralCodeService::createReferralCode($request, $user);

            return response()->jsonApi([
                'title' => "Referral code generate",
                'message' => 'Referral code / link was created successfully',
                'data' => [
                    'id' => $code->id,
                    'code' => $code->code,
                    //'link' => $code->link,
                    'note' => $code->note,
                    'is_default' => $code->is_default
                ]
            ]);
        } catch (ReferralCodeLimitException | Exception $e) {
            return response()->jsonApi([
                'title' => 'Referral code generate',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show one code and link
     *
     * @OA\Get(
     *     path="/referral-codes/{id}",
     *     description="Show referral code and link",
     *     tags={"Application | Referral Codes"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID referral code",
     *          example="1",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     )
     * )
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        try {
            $code = ReferralCode::find($id);

            return response()->jsonApi([
                'title' => "Get referral code info",
                'message' => 'Get referral code info with link',
                'data' => $code,
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => "Get referral code info",
                'message' => "Referral code #{$id} not found",
            ], 404);
        }
    }

    /**
     * Update referral link and code.
     *
     * @OA\Put(
     *     path="/referral-codes/{id}",
     *     description="Update referral code and link",
     *     tags={"Application | Referral Codes"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Referral code ID",
     *          example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="is_default",
     *                  type="boolean",
     *                  description="Is Default referral code / link. Accept 1, 0, true, false",
     *                  example="false"
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="string",
     *                  description="Note about referral code",
     *                  example=""
     *              ),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Save successfull"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param Request $request
     * @param                          $id
     *
     * @return mixed
     * @throws ValidationException
     */
    public function update(Request $request, $id): mixed
    {
        // Validate input data
        $validator = Validator::make($request->all(), ReferralCode::$rules);

        if ($validator->fails()) {
            return response()->jsonApi([
                'title' => 'Updating Referral code',
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Try to find referral code and update it
        try {
            $code = ReferralCode::findOrFail($id);

            $code->update($request->all());



            ReferralCode::byOwner()->byApplication()->update([
                'is_default' => false,
            ]);

            $this->setDefault($id);


            // Send response
            return response()->jsonApi([
                'title' => 'Updating Referral code',
                'message' => 'The referral code (link) has been successfully updated',
                'data' => ReferralCode::find($id),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'title' => 'Updating Referral code',
                'message' => 'Referral code not found',
            ], 404);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Updating Referral code',
                'message' => 'Referral code updated error: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove referral code
     *
     * @OA\Delete(
     *     path="/referral-codes/{id}",
     *     description="Delete referral code",
     *     tags={"Application | Referral Codes"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Delete referral code by ID",
     *         example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Referral code not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Code of error"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param $id
     *
     * @return mixed
     */
    public function destroy($id): mixed
    {
        try {
            ReferralCode::destroy($id);

            return response()->jsonApi([
                'title' => "Deleting success",
                'message' => 'The referral link field has been successfully deleted',
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Not found',
                'message' => "Referrals link #{$id} for deleted not found",
            ], 404);
        }
    }

    /**
     * Change the default link
     *
     * @OA\Put(
     *     path="/referral-codes/{id}/default",
     *     description="Set new referral code and link",
     *     tags={"Application | Referral Codes"},
     *
     *     security={{
     *         "bearerAuth": {},
     *         "apiKey": {}
     *     }},
     *
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of referral code",
     *          example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     )
     * )
     *
     * @param $id
     *
     * @return mixed
     */
    public function setDefault($id): mixed
    {
        try {
            $code = ReferralCode::find($id);

            // Reset defaults
            ReferralCodeService::defaultReset($code->user_id, $code->application_id);

            // Set new default code
            $code->update(['is_default' => true]);

            return response()->jsonApi([
                'title' => "Update was success",
                'message' => 'Changing the default а referral link was successful.',
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Operation not successful',
                'message' => "Changing the default a referral link was not successful.",
            ], 404);
        }
    }

    /**
     *  Get information on the referral link for a specific user
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getDataByUser(Request $request): mixed
    {
        try {
            // Get default referral code by user_id and application
            $referral_data = ReferralCode::byOwner()
                ->byApplication()
                ->where('is_default', 1)
                ->first();

            return response()->jsonApi([
                'title' => "Update was success",
                'message' => 'Changing the default а referral link was successful.',
                'data' => $referral_data,
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'title' => 'Not received list',
                'message' => "Data of referral code not found",
            ], 404);
        }
    }
}
