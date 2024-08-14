<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ODataProductController extends Controller
{
    /**
     * Display a listing of the products with OData support.
     *
     * @OA\Get(
     *     path="/api/odata/products",
     *     summary="Get Products",
     *     description="Retrieve a list of products with OData query support.",
     *     tags={"OData"},
     *     @OA\Parameter(
     *         name="$filter",
     *         in="query",
     *         description="Filter the results",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="$orderby",
     *         in="query",
     *         description="Order the results",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="$top",
     *         in="query",
     *         description="Limit the number of results",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="$skip",
     *         in="query",
     *         description="Skip the number of results",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="$expand",
     *         in="query",
     *         description="Expand related entities",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="category", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Apply filters if provided
        if ($request->has('$filter')) {
            $filter = $request->input('$filter');
            $this->applyFilter($query, $filter);
        }

        // Apply sorting if provided
        if ($request->has('$orderby')) {
            $orderby = $request->input('$orderby');
            list($field, $direction) = explode(' ', $orderby);
            $query->orderBy($field, $direction);
        }

        // Apply skipping if provided
        if ($request->has('$skip')) {
            $query->skip($request->input('$skip'));
        }

        // Apply limiting if provided
        if ($request->has('$top')) {
            $query->take($request->input('$top'));
        } else {
            // Ensure there is a default limit when skipping, to avoid SQL syntax issues
            $query->take(10);  // or any sensible default
        }

        // Apply expanding related entities if provided
        if ($request->has('$expand')) {
            $expand = $request->input('$expand');
            $query->with($expand);
        }

        return response()->json($query->get());
    }

    protected function applyFilter($query, $filter)
    {
        if (preg_match('/(\w+)\s+(eq|ne|gt|lt|ge|le|like)\s+(.+)/', $filter, $matches)) {
            $field = $matches[1];
            $operator = $matches[2];
            $value = $matches[3];

            switch ($operator) {
                case 'eq':
                    $query->where($field, '=', trim($value, "'"));
                    break;
                case 'ne':
                    $query->where($field, '!=', trim($value, "'"));
                    break;
                case 'gt':
                    $query->where($field, '>', $value);
                    break;
                case 'lt':
                    $query->where($field, '<', $value);
                    break;
                case 'ge':
                    $query->where($field, '>=', $value);
                    break;
                case 'le':
                    $query->where($field, '<=', $value);
                    break;
                case 'like':
                    $query->where($field, 'like', '%' . trim($value, "'") . '%');
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @OA\Post(
     *     path="/api/odata/products",
     *     summary="Create Product",
     *     description="Create a new product.",
     *     tags={"OData"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "category_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully."
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    /**
     * Update the specified product in storage.
     *
     * @OA\Put(
     *     path="/api/odata/products/{id}",
     *     summary="Update Product",
     *     description="Update an existing product.",
     *     tags={"OData"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "category_id"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product, 200);
    }

    /**
     * Remove the specified product from storage.
     *
     * @OA\Delete(
     *     path="/api/odata/products/{id}",
     *     summary="Delete Product",
     *     description="Delete a product by ID.",
     *     tags={"OData"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(null, 204);
    }
}
