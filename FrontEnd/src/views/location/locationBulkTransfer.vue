<template>
  <div class="app-container">
    <h1>Location Bulk Transfer</h1>
    <el-divider />

    <h2>Old Location</h2>
    <p>
      <el-input
        ref="locNrInput"
        v-model="oldLocNr"
        placeholder="Please input"
        @keyup.enter.native="selectLocation"
      >
        <el-button slot="append" icon="el-icon-search" @click="selectLocation" />
      </el-input>
    </p>
    <p>
      <el-cascader-panel
        v-model="oldLocNr"
        :options="locations"
        :props="{
          emitPath: false,
          value: 'LocNr',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
        @change="getSummary()"
      />
    </p>

    <h2>New Location</h2>

    <p>
      <el-input
        ref="locNrInput"
        v-model="newLocNr"
        placeholder="Please input"
        @keyup.enter.native="selectLocation"
      >
        <el-button slot="append" icon="el-icon-search" @click="selectLocation" />
      </el-input>
    </p>
    <p>
      <el-cascader-panel
        v-model="newLocNr"
        :options="locations"
        :props="{
          emitPath: false,
          value: 'LocNr',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
      />
    </p>
    <p>
      <el-button type="primary" @click="transfer">Transfer All Items</el-button>
      <el-button type="danger" @click="resetForm">Clear</el-button>
    </p>
    <h2>Items</h2>
    <p>
      <el-table :data="itemList" border style="width: 100%">
        <el-table-column prop="Item" label="Item Nr." width="120" />
        <el-table-column prop="Category" label="Category" width="120" />
        <el-table-column prop="Description" label="Description" />
      </el-table>
    </p>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import Cookies from 'js-cookie'

export default {
  name: 'LocationBulkTransfer',
  components: {},
  data() {
    return {
      oldLocNr: null,
      newLocNr: null,

      locations: null,
      itemList: []
    }
  },
  mounted() {
    this.getLocations()
  },
  methods: {
    resetForm() {
      this.oldLocNr = null
      this.newLocNr = null
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
        params: { LocationNr: this.oldLocNr }
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
    },
    transfer() {
      if (!this.oldLocNr) {
        this.$message({
          showClose: true,
          message: 'No old location specified',
          duration: 3000,
          type: 'warning'
        })
        return
      }
      if (!this.newLocNr) {
        this.$message({
          showClose: true,
          message: 'No new location specified',
          duration: 3000,
          type: 'warning'
        })
        return
      }

      requestBN({
        method: 'post',
        url: '/location/bulkTransfer',
        data: { OldLocationNr: this.oldLocNr, NewLocationNr: this.newLocNr }
      }).then(response => {
        if (response.error == null) {
          this.resetForm()
          this.$message({
            showClose: true,
            message: 'Transfer Successful',
            type: 'success'
          })
          this.resetForm()
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
      })
    }
  }
}
</script>
