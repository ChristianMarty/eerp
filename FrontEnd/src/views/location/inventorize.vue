<template>
  <div class="app-container">
    <h1>Inventorize</h1>
    <el-divider />
    <p>Check the contents of a location.</p>
    <h2>Location</h2>

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
          value: 'LocNr',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
      />
    </p>
    <el-button type="primary" @click="loadItems">Load</el-button>

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

    <h2>Missing Items</h2>
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
    <h2>Found Items</h2>
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
    <h2>Wrong Items</h2>
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
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import Cookies from 'js-cookie'

export default {
  name: 'LocationAssignment',
  components: {},
  data() {
    return {
      inputItemNr: null,
      inputLocNr: null,

      locations: null,
      itemList: []
    }
  },
  mounted() {
    this.getLocations()
  },
  methods: {
    resetForm() {
      this.inputItemNr = null
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
    selectLocation() {},
    addItem() {
      requestBN({
        url: '/util/itemDescription',
        methood: 'get',
        params: { Item: this.inputItemNr }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else if (response.data.length == 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          if (response.data.Movable == true) {
            this.itemList.push(response.data)
          } else {
            this.$message({
              showClose: true,
              message: 'Item is not Movable!',
              type: 'warning'
            })
          }
        }
      })
      this.inputItemNr = null
      this.$refs.itemNrInput.focus()
    },
    loadItems() {},
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

      var transferData = {
        LocNr: this.inputLocNr,
        ItemList: []
      }

      this.itemList.forEach(element => {
        transferData.ItemList.push(element.Item)
      })

      requestBN({
        method: 'post',
        url: '/location/transfer',
        data: { data: transferData }
      }).then(response => {
        if (response.error == null) {
          this.resetForm()
          this.$message({
            showClose: true,
            message: 'Item Transfer Successful',
            type: 'success'
          })
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
