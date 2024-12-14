<template>
    <v-card flat title="Users">
      <template v-slot:text>
        <v-text-field v-model="search" label="Search" prepend-inner-icon="mdi-magnify" single-line variant="outlined"
          hide-details></v-text-field>
      </template>
  
      <v-data-table :headers="userHeader" :items="users" :search="search"></v-data-table>
    </v-card>
  </template>
  
  <script>
  export default {
    data() {
      return {
        search: '',
        users: [], // Will be populated with users
        userHeader: [
          { text: 'Id', value: 'id' },
          { text: 'Name', value: 'name' },
          { text: 'Email', value: 'email' },
          { text: 'Created at', value: 'created_at' },
          { text: 'Updated at', value: 'updated_at' },
          // Add other headers as needed for recipes
        ],
      };
    },
  
  
    methods: {
      getUser() {
  
        axios.get('api/users')
          .then(response => {
            this.users = response.data;
            // this.users = response.data.users;
          })
          .catch(error => {
            console.error(error);
            // Handle errors
          });
      },
    },
  
    created() {
      this.getUser();
    }
  
  };
  </script>