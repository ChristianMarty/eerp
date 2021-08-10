<template>
  <div class="app-container">
    <h1>Location Summary</h1>
    <el-divider />

    <p>
      <el-input
        ref="locNrInput"
        v-model="inputLocNr"
        placeholder="Please input"
        @keyup.enter.native="getSummary"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="getSummary"
        />
      </el-input>
    </p>
    <p>
      <el-cascader-panel
        v-model="inputLocNr"
        :options="locations"
        :props="{
          emitPath: false,
          value: 'LocNr',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
        @change="getSummary"
      />
    </p>
    <h2>{{ itemList.length }} Items Found</h2>

    <p>
      <el-table :data="itemList" border style="width: 100%">
        <el-table-column prop="Item" label="Item Nr." width="120" />
        <el-table-column prop="Category" label="Category" width="120" />
        <el-table-column
          prop="Description"
          label="Description"
        />
      </el-table>
    </p>
    <p>
      <el-button type="danger" @click="resetForm">Clear</el-button>
    </p>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'LocationAssignment',
  data() {
    return {
      inputLocNr: null,
      locations: null,
      itemList: []
    }
  },
  mounted() {
    if (this.$route.params.LocationNr != null) {
      if (this.$route.params.LocationNr != ':LocationNr(.*)') {
        this.inputLocNr = this.$route.params.LocationNr
        this.getSummary()
      }
    }

    this.getLocations()
  },
  methods: {
    resetForm() {
      this.inputLocNr = null
      this.itemList = []
    },
    getLocations() {
      requestBN({
        url: '/location',
        methood: 'get'
      }).then(response => {
        this.locations = response.data
      })
    },
    getSummary() {
      requestBN({
        url: '/location/summary',
        methood: 'get',
        params: { LocationNr: this.inputLocNr }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.itemList = response.data
        }
      })
      this.inputItemNr = null
    }
  }
}
</script>
