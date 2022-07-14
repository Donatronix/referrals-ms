<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class TransactionsController
 *
 * @package App\Api\V1\Controllers
 */
class TransactionsController extends Controller
{
    /**
     *  Display a listing of the transactions
     *
     * @OA\Get(
     *     path="/admin/transactions",
     *     description="Get all transactions",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *          "default" :{
     *              "ManagerRead",
     *              "Transaction",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Output data",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Description of data parameters",
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     description="User id",
     *                     example=500,
     *                 ),
     *                 @OA\Property(
     *                     property="user_plan",
     *                     type="string",
     *                     description="User plan",
     *                     example="Basic",
     *                 ),
     *                 @OA\Property(
     *                     property="reward",
     *                     type="integer",
     *                     description="User reward",
     *                     example=200,
     *                 ),
     *                 @OA\Property(
     *                     property="currency",
     *                     type="string",
     *                     description="User currency",
     *                     example="$",
     *                 ),
     *                 @OA\Property(
     *                     property="operation_name",
     *                     type="string",
     *                     description="Name of operation being carried out",
     *                     example="Store",
     *                 ),
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="Uuid transaction not found"
     *              ),
     *              @OA\Property(
     *                  property="user_plane",
     *                  type="string",
     *                  description="User plan not found"
     *              ),
     *              @OA\Property(
     *                  property="reward",
     *                  type="integer",
     *                  description="Reward not found"
     *              ),
     *              @OA\Property(
     *                  property="Currency",
     *                  type="string",
     *                  description="Currency not found"
     *              ),
     *              @OA\Property(
     *                  property="operation_name",
     *                  type="string",
     *                  description="No operation name declared"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            $transactions = Transaction::query()->orderBy('created_at')->paginate($request->get('limit', config('settings.pagination_limit')));

            return response()->jsonApi(
                [
                    'type' => 'success',
                    'title' => 'Operation was success',
                    'message' => 'The data was displayed successfully',
                    'data' => $transactions->toArray(),
                ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all transactions",
                'data' => null,
            ], 404);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Update failed",
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }

    /**
     *  Display a transaction.
     *
     * @OA\Get(
     *     path="/admin/transactions/{id}",
     *     description="Get transaction by id",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *          "default" :{
     *              "ManagerRead",
     *              "Transaction",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Output data",
     *
     *         @OA\JsonContent(
     *              type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Description of data parameters",
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     description="User id",
     *                     example=500,
     *                 ),
     *                 @OA\Property(
     *                     property="user_plan",
     *                     type="string",
     *                     description="User plan",
     *                     example="Basic",
     *                 ),
     *                 @OA\Property(
     *                     property="reward",
     *                     type="integer",
     *                     description="User reward",
     *                     example=200,
     *                 ),
     *                 @OA\Property(
     *                     property="currency",
     *                     type="string",
     *                     description="User currency",
     *                     example="$",
     *                 ),
     *                 @OA\Property(
     *                     property="operation_name",
     *                     type="string",
     *                     description="Name of operation being carried out",
     *                     example="Store",
     *                 ),
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="user_id",
     *                  type="string",
     *                  description="Uuid transaction not found"
     *              ),
     *              @OA\Property(
     *                  property="user_plane",
     *                  type="string",
     *                  description="User plan not found"
     *              ),
     *              @OA\Property(
     *                  property="reward",
     *                  type="integer",
     *                  description="Reward not found"
     *              ),
     *              @OA\Property(
     *                  property="Currency",
     *                  type="string",
     *                  description="Currency not found"
     *              ),
     *              @OA\Property(
     *                  property="operation_name",
     *                  type="string",
     *                  description="No operation name declared"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id): mixed
    {
        // Get transaction model
        try {
            // Get and return transaction data
            $transaction = Transaction::query()->findOrFail($id)->toArray();

            return response()->jsonApi(
                [
                    'type' => 'success',
                    'title' => 'Operation was success',
                    'message' => 'The data was displayed successfully',
                    'data' => $transaction,
                ],
                200);

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Transaction not found",
                'message' => "Error displaying transaction",
                'data' => null,
            ], 404);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Display failed",
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }

    /**
     * New transaction
     *
     * @OA\Post(
     *     path="/admin/transactions",
     *     summary="Create new transaction",
     *     description="Create new transaction",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *         "passport": {
     *             "ManagerRead",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *     @OA\Parameter(
     *          name="user_plan",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string",
     *          ),
     *     ),
     *     @OA\Parameter(
     *          name="reward",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="integer",
     *          ),
     *     ),
     *     @OA\Parameter(
     *          name="currency",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *     @OA\Parameter(
     *          name="operation_name",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Bad Request"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return Transaction|JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request): Transaction|JsonResponse
    {
        try {
            $rules = [
                'user_id' => 'required',
                'user_plan' => 'required|string',
                'reward' => 'required|numeric',
                'currency' => 'required|string',
                'operation_name' => 'required|string',
            ];

            $validated = $this->validate($request, $rules);

            $transaction = Transaction::query()->create($validated);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Transaction",
                "message" => "Transaction added successfully!",
                'data' => $transaction,

            ], 200);
        } catch (Throwable $th) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Transaction not added",
                'message' => $th->getMessage(),
                'data' => null,
            ], 400);
        }

    }

    /**
     * Update the specified resource in storage
     *
     * @OA\Put(
     *     path="/admin/transactions/{id}",
     *     summary="update user",
     *     description="update user",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *         "passport": {
     *             "ManagerRead",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="user_plan",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="reward",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="currency",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="operation_name",
     *          required=true,
     *          in="query",
     *          @OA\Schema (
     *              type="string"
     *          )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  description="Danger"
     *              ),
     *              @OA\Property(
     *                  property="title",
     *                  type="string",
     *                  description="Message title"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Transaction parameter list",
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                     description="Transaction uuid",
     *                     example="9443407b-7eb8-4f21-8a5c-9614b4ec1bf9",
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     description="user id",
     *                     example="Vasya",
     *                 ),
     *                 @OA\Property(
     *                     property="user_plan",
     *                     type="string",
     *                     description="user plan",
     *                     example="Basic",
     *                 ),
     *                 @OA\Property(
     *                     property="reward",
     *                     type="string",
     *                     description="User reward",
     *                     example="100000",
     *                 ),
     *                 @OA\Property(
     *                     property="currency",
     *                     type="string",
     *                     description="Transaction currency",
     *                     example="$",
     *                 ),
     *                 @OA\Property(
     *                     property="operation_name",
     *                     type="string",
     *                     description="Operation name",
     *                     example="Type of transaction operation",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not found"
     *     )
     * )
     *
     * @param Request $request
     * @param $id
     *
     * @return mixed
     * @throws ValidationException
     */
    public function update(Request $request, $id): mixed
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'user_plan' => 'required|string',
                'reward' => 'required|numeric',
                'currency' => 'required|string',
                'operation_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $validated = $validator->validated();

            $transaction = Transaction::findOrFail($id);

            $transaction->update($validated);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Transaction",
                "message" => "Updated successfully",
                "data" => $transaction->toArray(),
            ], 200);

        } catch (Throwable $th) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Transaction failed",
                'message' => $th->getMessage(),
                'data' => null,
            ], 400);
        }
    }

    /**
     *  Delete transaction record
     *
     * @OA\Delete(
     *     path="/admin/transactions/{id}",
     *     description="Delete transaction",
     *     tags={"Admin / Transactions"},
     *
     *     security={{
     *          "default" :{
     *              "ManagerRead",
     *              "transaction",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="transaction id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Output data",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Success or error message",
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="Uuid transaction not found"
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     *
     * Remove the specified resource from storage.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function destroy(mixed $id): mixed
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Delete failed",
                'message' => "Transaction does not exist",
                'data' => null,
            ], 404);
        } catch (Throwable $th) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Delete failed",
                'message' => $th->getMessage(),
                'data' => null,
            ], 404);
        }
        return response()->jsonApi([
            'type' => 'success',
            'title' => 'Operation was a success',
            'message' => 'Transaction was deleted successfully',
        ], 200);
    }
}
