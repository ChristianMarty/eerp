<template>
  <div class="app-container">
    <h1>Hallo {{ user.name }}</h1>

    <h2>Roles</h2>

    <p v-for="(value, name) in userData.rolesJson">
      {{ name }} {{ value }}
    </p>

    <h2>Settings</h2>

    <p v-for="(value, name) in userData.settings">
      {{ name }} {{ value }}
    </p>

  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

import { mapGetters } from 'vuex'

export default {
  name: 'Profile',
  data() {
    return {
      user: {},
      userData: {},
      settings: {}
    }
  },
  computed: {
    ...mapGetters(['name', 'avatar', 'roles'])
  },
  created() {
    this.getUser()
    this.getUserData()
  },
  methods: {
    getUser() {
      this.user = {
        name: this.name,
        role: this.roles.join(' | '),
        avatar: this.avatar
      }
    },
    getUserData() {
      requestBN({
        url: 'user/info',
        methood: 'get'
      }).then(response => {
        this.userData = response.data
      })
    }
  }
}
</script>
