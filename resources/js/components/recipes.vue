<template>
  <v-card flat title="Recipes">
    <template v-slot:text>
      <v-text-field v-model="search" label="Search" prepend-inner-icon="mdi-magnify" single-line variant="outlined"
        hide-details></v-text-field>
    </template>

    <v-data-table :headers="recipeHeaders" :items="recipe" :search="search">

      <template v-slot:item.image="{ item }">
        <v-img :src="item.image_url" alt="Recipe Image" width="50" height="50"></v-img>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-row>
          <div class="btn">
            <v-btn class="custom-btn-edit">
              <a :href="`/edit?id=${item.id}`">Edit</a>
            </v-btn>

            <v-btn class="custom-btn-delete" @click="deleteRecipe(item.id)" text color="error">Delete</v-btn>
          </div>
        </v-row>
      </template>

    </v-data-table>

  </v-card>
</template>
  
<script>
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      search: '',
      recipe: [], // Will be populated with recipes
      recipeHeaders: [
        { text: 'Id', value: 'id' },
        { text: 'Image', value: 'image' },
        { text: 'Name', value: 'recipe_name' },
        { text: 'Description', value: 'recipe_description' },
        { text: 'Actions', value: 'actions' },
        // Add other headers as needed for recipes
      ],
    };
  },

  methods: {
    fetchRecipes() {
      axios.get('api/recipes')
        .then(response => {
          // Assuming your backend returns the image_url along with other recipe data
          this.recipe = response.data.map(recipe => ({
            ...recipe,
            // image_url: `/storage/recipe_images/${recipe.image}`, //This is for Local Storage // Adjust this based on your storage configuration
            image_url: `https://passafund-staging.sgp1.digitaloceanspaces.com/recipe_images/${recipe.image}`, // Adjust this based on your storage configuration
          }));
        })
        .catch(error => {
          console.error(error);
          // Handle errors
        });
    },

    deleteRecipe(recipeId) {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      })
        .then((result) => {
          if (result.isConfirmed) {
            // Send a DELETE request to the backend to delete the recipe
            axios.get(`api/recipes/${recipeId}`)
              .then(response => {
                // Assuming the backend returns a success message
                console.log('Recipe deleted successfully:', response.data);

                // Optionally, you can update the local recipe array to reflect the deletion
                this.recipe = this.recipe.filter(recipe => recipe.id !== recipeId);
              })
            Swal.fire({
              title: "Deleted!",
              text: "...",
              icon: "success"
            });
          }
        })
        .catch(error => {
          console.error('Error deleting recipe:', error);
          // Handle errors
          Swal.fire({
            title: "Error",
            text: "Failed to delete the recipe.",
            icon: "error"
          });
        });
    },
  },

  created() {
    this.fetchRecipes();
  }
};
</script>