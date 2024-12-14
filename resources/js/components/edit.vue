<template>
    <div align="center">
        <v-card class="custom-bg col-md-8" height="auto">
            <v-form @submit.prevent="updateRecipe" enctype="multipart/form-data">
                <img :src="recipe.image_url" alt="Recipe Image"
                    style="max-width: 50%; max-height: 50%; border-radius: 30px;">
                <br>
                <br>
                <br>
                <div>
                    <input type="file" @change="onFileChange" accept="image/*">
                </div>
                <!-- <v-file-input type="file" @change="onFileChange" accept="image/*"></v-file-input> -->
                <v-text-field v-model="recipe.recipe_name" label="Recipe Name"></v-text-field>
                <v-text-field v-model="recipe.recipe_description" label="Description"></v-text-field>
                <v-btn type="submit" block class="mt-2" style="border-radius: 30px;">Update</v-btn>
            </v-form>
        </v-card>
    </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            recipe: {
                image_url: '',
                recipe_name: '',
                recipe_description: ''
            },
            fileInput: null // Added fileInput data property
        };
    },

    methods: {
        async fetchRecipeData() {
            const urlParams = new URLSearchParams(window.location.search);
            const recipeId = urlParams.get('id');

            try {
                const response = await axios.get(`api/edit/${recipeId}`);
                this.recipe = response.data;
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async updateRecipe() {
            const urlParams = new URLSearchParams(window.location.search);
            const recipeId = urlParams.get('id');
            const formData = new FormData();
            formData.append('recipe_name', this.recipe.recipe_name);
            formData.append('recipe_description', this.recipe.recipe_description);
            if (this.fileInput) {
                formData.append('image', this.fileInput); // Use fileInput only if it exists
            }

            try {
                const response = await axios.post(`api/update/${recipeId}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                console.log('Recipe updated:', response.data);
                Swal.fire({
                    title: 'Success!',
                    text: 'Recipe updated successfully.',
                    icon: 'success',
                    timer: 2000,
                });
                setTimeout(() => {
                    window.location.pathname = '/home';
                }, 2000);
            }
            
            catch (error) {
                console.error('Error updating recipe:', error);
                Swal.fire({
                    title: "Error",
                    text: "Failed to update the recipe.",
                    icon: "error"
                });
            }
        },

        onFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.fileInput = file; // Update fileInput property
                this.previewImage(file);
            }
        },

        previewImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.recipe.image_url = e.target.result; // Update recipe.image_url
            };
            reader.readAsDataURL(file);
        },
    },

    mounted() {
        this.fetchRecipeData();
    },
};
</script>

<style scoped>
.custom-bg {
    border-radius: 30px;
    background-color: rgb(211, 211, 211);
}
</style>
