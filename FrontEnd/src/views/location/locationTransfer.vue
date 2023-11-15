<template>
  <div class="app-container">
    <h1>Location Transfer</h1>
    <el-divider />

    <h2>New Location</h2>
    <p>Select the location to which the Items should be transfered.</p>

    <p>
      <el-input
        ref="locNrInput"
        v-model="inputLocNr"
        placeholder="Please input"
        @keyup.enter.native="selectLocation"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="selectLocation"
        />
      </el-input>
    </p>
    <p>
      <el-cascader-panel
        v-model="inputLocNr"
        :options="locations"
        :props="{
          emitPath: false,
          value: 'LocationBarcode',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
      />
    </p>
    <h2>Items</h2>
    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputItemNr"
        placeholder="Please input"
        @keyup.enter.native="addItem"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="addItem"
        />
      </el-input>
    </p>
    <p>
      <el-table :data="itemList" border style="width: 100%">
        <el-table-column prop="Item" label="Item Nr." width="120" />
        <el-table-column prop="Category" label="Category" width="120" />
        <el-table-column
          prop="Description"
          label="Description"
        />
        <el-table-column
          prop="Location"
          label="Current Location"
        />
      </el-table>
    </p>
    <p>
      <el-button type="primary" @click="transfer">Transfer Items</el-button>
      <el-button type="danger" @click="resetForm">Clear</el-button>
    </p>
  </div>
</template>

<script>
import Location from '@/api/location'
const location = new Location()

import Utility from '@/api/utility'
const utility = new Utility()

export default {
  name: 'LocationAssignment',
  components: {},
  data() {
    return {
      locations: Object.assign({}, location.searchReturn),
      inputItemNr: null,
      inputLocNr: null,
      itemList: []
    }
  },
  async mounted() {
    this.locations = await location.search()
  },
  methods: {
    resetForm() {
      this.inputItemNr = null
      this.inputLocNr = null
      this.itemList = []
    },
    selectLocation() {},
    addItem() {
      utility.description(this.inputItemNr).then(response => {
        if (response.length === 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          if (response.Movable === true) {
            this.itemList.unshift(response)
          } else {
            this.$message({
              showClose: true,
              message: 'Item is not Movable!',
              type: 'warning'
            })
          }
        }
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
      this.inputItemNr = null
      this.$refs.itemNrInput.focus()
    },
    transfer() {
      if (!this.inputLocNr) {
        this.$message({
          showClose: true,
          message: 'No Location specified',
          duration: 3000,
          type: 'warning'
        })
        return
      }

      if (!this.itemList.length) {
        this.$message({
          showClose: true,
          message: 'Nothing to transfer',
          duration: 3000,
          type: 'warning'
        })
        return
      }

      var itemList = []
      this.itemList.forEach(element => {
        itemList.push(element.Item)
      })

      location.transfer(this.inputLocNr, itemList).then(response => {
        this.resetForm()
        this.$message({
          showClose: true,
          message: 'Item Transfer Successful',
          type: 'success'
        })
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
