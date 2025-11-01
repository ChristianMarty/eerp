<template>
  <div class="app-container">
    <h1>Hallo {{ userData.name }}</h1>

    <h2>Roles</h2>
    <p v-for="(value, name) in userData.roles">
      {{ name }} {{ value }}
    </p>

    <h2>Settings</h2>
    <p v-for="(value, name) in userData.settings.Default">
      {{ name }} {{ value }}
    </p>

  </div>
</template>

<script>

import { User } from '@/api/user'
const user = new User()

export default {
  name: 'Profile',
  data() {
    return {
      userData: {}
    }
  },
  created() {
    this.getUserData()
  },
  methods: {
    getUserData() {
      user.info().then(response => {
        this.userData = response
      })
    }
  }
}
</script>
