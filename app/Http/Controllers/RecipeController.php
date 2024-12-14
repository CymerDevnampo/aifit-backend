<?php

namespace App\Http\Controllers;

use App\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Add this import for logging
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;



class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // Example in your RecipeController


    // public function index() //This is for Local Storage
    // {
    //     $recipes = Recipe::all();

    //     // Generate image URLs
    //     foreach ($recipes as $recipe) {
    //         $recipe->image_url = asset('storage/recipe_images/' . $recipe->image);
    //     }

    //     return response()->json($recipes);
    // }

    // public function index()
    // {
    //     $recipes = Recipe::all();

    //     // Replace local URL with DigitalOcean Spaces URL
    //     foreach ($recipes as $recipe) {
    //         $recipe->image_url = 'https://passafund-staging.sgp1.digitaloceanspaces.com/recipe_images/' . $recipe->image;
    //     }

    //     return response()->json($recipes);
    // }

    public function index() //This is for Supabase
    {
        $recipes = Recipe::all();

        // Generate image URLs for Supabase storage
        foreach ($recipes as $recipe) {
            // Check if the image field contains a full URL
            if (filter_var($recipe->image, FILTER_VALIDATE_URL)) {
                $recipe->image_url = $recipe->image;
            } else {
                // If it's not a full URL, construct the Supabase URL
                $recipe->image_url = env('SUPABASE_PROJECT_URL') . '/storage/v1/object/public/images/' . env('SUPABASE_S3_BUCKET') . '/' . $recipe->image;
            }
        }
        return response()->json($recipes);
    }


    public function display() //This is for Local Storage
    {
        $recipes = Recipe::take(2)->get(); // Retrieve only two recipes

        // Generate image URLs
        foreach ($recipes as $recipe) {
            $recipe->image_url = asset('storage/recipe_images/' . $recipe->image);
        }

        return response()->json($recipes);
    }

    // public function display()
    // {
    //     $recipes = Recipe::take(2)->get(); // Retrieve only two recipes

    //     // Generate image URLs
    //     foreach ($recipes as $recipe) {
    //         $recipe->image_url = 'https://passafund-staging.sgp1.digitaloceanspaces.com/recipe_images/' . $recipe->image;
    //     }

    //     return response()->json($recipes);
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    // public function store(Request $request) //This is for Local Storage
    // {
    //     $validatedData = $request->validate([
    //         'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:20480',
    //         'recipe_name' => 'required|string|max:255',
    //         'recipe_description' => 'required|string',
    //     ]);
    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $path = $image->store('recipe_images', 'public');
    //         $validatedData['image'] = basename($path);  // Save only the filename, not the full path
    //     }

    //     // Store the validated data in the database
    //     Recipe::create($validatedData);

    //     return response()->json(['message' => 'Recipe created successfully'], 201);
    // }

    // public function store(Request $request) //This is for DigitalOcean Spaces
    // {
    //     $validatedData = $request->validate([
    //         'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:20480',
    //         'recipe_name' => 'required|string|max:255',
    //         'recipe_description' => 'required|string',
    //     ]);

    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');

    //         // Try uploading the image to DigitalOcean Spaces
    //         try {
    //             $path = Storage::disk('s3')->put('recipe_images', $image, 'public');
    //             // Get the full URL of the uploaded image
    //             $url = Storage::disk('s3')->url($path);
    //             // Update the validatedData with the URL of the uploaded image
    //             $validatedData['image'] = $url;
    //         } catch (\Exception $e) {
    //             // Handle the exception (e.g., log the error, return an error response)
    //             return response()->json(['error' => 'Failed to upload image.'], 500);
    //         }
    //     }

    //     // Create the recipe record in the database
    //     Recipe::create($validatedData);

    //     // Return a JSON response indicating success
    //     return response()->json(['message' => 'Recipe created successfully'], 201);
    // }

    public function store(Request $request) //This is for SupaBase
    {
        $validatedData = $request->validate([
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:20480',
            'recipe_name' => 'required|string|max:255',
            'recipe_description' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = time() . '_' . $image->getClientOriginalName();

            try {
                $client = new Client();
                $response = $client->request('POST', env('SUPABASE_PROJECT_URL') . '/storage/v1/object/images/' . $fileName, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('SUPABASE_API_KEY'),
                        'Content-Type' => $image->getMimeType(),
                    ],
                    'body' => fopen($image->getPathname(), 'r'),
                ]);

                if ($response->getStatusCode() == 200) {
                    $publicUrl = env('SUPABASE_PROJECT_URL') . '/storage/v1/object/public/images/' . $fileName;
                    $validatedData['image'] = $publicUrl;
                } else {
                    Log::error('Supabase upload error', [
                        'status' => $response->getStatusCode(),
                        'body' => $response->getBody()->getContents(),
                    ]);
                    return response()->json(['error' => 'Failed to upload image to Supabase: ' . $response->getBody()->getContents()], 500);
                }
            } catch (\Exception $e) {
                Log::error('Supabase upload error', ['message' => $e->getMessage()]);
                return response()->json(['error' => 'Failed to upload image to Supabase: ' . $e->getMessage()], 500);
            }
        }

        Recipe::create($validatedData);

        return response()->json(['message' => 'Recipe created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show($id) //This is for Local Storage
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }
        $recipe->image_url = asset('storage/recipe_images/' . $recipe->image);

        if (!Storage::disk('public')->exists('recipe_images/' . $recipe->image)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        return response()->json($recipe);
    }

    // public function show($id)
    // {
    //     $recipe = Recipe::find($id);

    //     if (!$recipe) {
    //         return response()->json(['error' => 'Recipe not found'], 404);
    //     }

    //     // Generate the image URL using DigitalOcean Spaces URL
    //     $recipe->image_url = 'https://passafund-staging.sgp1.digitaloceanspaces.com/recipe_images/' . $recipe->image;

    //     return response()->json($recipe);
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id) //This is for Local Storage
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        $recipe->image_url = asset('storage/recipe_images/' . $recipe->image);

        if (!Storage::disk('public')->exists('recipe_images/' . $recipe->image)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        return response()->json($recipe);
    }

    // public function edit($id)
    // {
    //     $recipe = Recipe::find($id);

    //     if (!$recipe) {
    //         return response()->json(['error' => 'Recipe not found'], 404);
    //     }

    //     $recipe->image_url = 'https://passafund-staging.sgp1.digitaloceanspaces.com/recipe_images/' . $recipe->image;
    //     return response()->json($recipe);
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id) //This is for Local Storage
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        $validatedData = $request->validate([
            'recipe_name' => 'required|string|max:255',
            'recipe_description' => 'required|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:20480', // Add image validation rules
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store('recipe_images', 'public');
            $validatedData['image'] = basename($path);  // Save only the filename, not the full path
        } else {
            // If no image is uploaded, retain the existing image
            $validatedData['image'] = $recipe->image;
        }

        $recipe->update($validatedData);

        return response()->json(['message' => 'Recipe updated successfully']);
    }

    // public function update(Request $request, $id)
    // {
    //     $recipe = Recipe::find($id);

    //     if (!$recipe) {
    //         return response()->json(['error' => 'Recipe not found'], 404);
    //     }

    //     $validatedData = $request->validate([
    //         'recipe_name' => 'required|string|max:255',
    //         'recipe_description' => 'required|string',
    //         'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:20480', // Add image validation rules
    //     ]);

    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         $path = Storage::disk('s3')->put('recipe_images', $image, 'public');
    //         $validatedData['image'] = basename($path);  // Save only the filename, not the full path
    //     } else {
    //         // If no image is uploaded, retain the existing image
    //         $validatedData['image'] = $recipe->image;
    //     }

    //     $recipe->update($validatedData);

    //     return response()->json(['message' => 'Recipe updated successfully']);
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Recipe::findOrFail($id)->delete();

        return response()->json(['message' => 'Recipe deleted successfully']);
    }
}
