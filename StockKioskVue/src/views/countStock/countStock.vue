<template>
  <div class="card">
    <router-link to="/">
      <el-button size="large">Cancel</el-button>
    </router-link>

    <el-card class="box-card">

      <el-input
        ref="stockNumberInput"
        v-model="stockNumberInput"
        placeholder="Stock Barcode (STK-xxxx)"
        @keyup.enter.native="searchItem()"
      >
        <el-button slot="append" icon="el-icon-search" @click="searchItem()" />
      </el-input>

      <template v-if="showItem">

        <el-divider/>

        <el-form label-width="150px">
          <el-form-item label="Item Code:">
            {{ currentItem.ItemCode }}
          </el-form-item>
          <el-form-item label="Part:">
            {{ currentItem.Part.ManufacturerName }} - {{ currentItem.Part.ManufacturerPartNumber }}
          </el-form-item>
          <el-form-item label="Current Quantity:">
            {{ currentItem.Quantity.Quantity }}
          </el-form-item>
          <el-form-item label="Quantity:">
            <el-input-number
                ref="stockRemoveQuantityInput"
                v-model="newCount"
                :min="0"
                :max="Number(10000)"
                @keyup.enter.native="confirmAndClose()"
            />
          </el-form-item>

          <el-form-item label="Note:">
            <el-input
                v-model="note"
                type="textarea"
                @keyup.enter.native="confirmAndClose()"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="confirmAndClose()">Confirm</el-button>
            <el-button @click="closeItem()">Cancel</el-button>
          </el-form-item>
        </el-form>

      </template>
    </el-card>
  </div>
</template>

<script lang="ts">

import Stock from '../../api/stock'
import {ElMessage} from "element-plus";
const stock = new Stock()


export default {
  name: 'RemoveStock',
  components: {
  },
  data() {
      return {
        stockNumberInput: "",
        showItem: false,
        currentItem: {},

        newCount: 0,
        note: ""
      }
  },
  mounted() {
    this.$refs.stockNumberInput.focus()
  },
  methods: {
      searchItem(){
        this.showItem = false
        stock.get(this.stockNumberInput).then(response => {
          if(response === null) {
            ElMessage({
              showClose: true,
              message: 'Stock number not found.',
              type: 'warning'
            })
          }else{
            this.currentItem = response
            this.showItem = true
            this.newCount = response.Quantity.Quantity
          }
        }).catch(response => {
          ElMessage({
            showClose: true,
            message: response,
            duration: 60000,
            type: 'error'
          })
        })

    },
    closeItem(){
      this.showItem = false
      this.currentItem = {}
      this.stockNumberInput = ''
      this.$refs.stockNumberInput.focus()
    },
    confirmAndClose() {
      stock.count(this.stockNumberInput, this.newCount, this.note).then(response => {
          ElMessage({
            showClose: true,
            message: "The quantity was updated successfully",
            type: 'success'
          })
        this.closeItem()
      }).catch(response => {
        ElMessage({
          showClose: true,
          message: response,
          duration: 60000,
          type: 'error'
        })
      })
    }
  }
}
</script>

<style scoped>
.text {
  font-size: 14px;
}
.item {
  padding: 18px 0;
}
.box-card {
  width: 100%;
  height: 100%;
  margin-top: 20px;
}
</style>


