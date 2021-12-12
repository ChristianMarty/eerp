<template>
  <div class="app-container">
    <h1>Hallo {{ user.name }}</h1>

    <h2>Roles</h2>

    <h3 v-for="(value, name) in userData.rolesJson">
      {{ name }} {{ value }}
    </h3>

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
      userData: {}
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
