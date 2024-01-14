<template>
  <div class="scale-container">
    <el-form label-width="150px">
      <el-form-item label="Selected scale:">
        <el-select v-model="selectedScaleId">
          <el-option v-for="item in scales" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
        </el-select>
      </el-form-item>

      <el-form-item label="Calibration:">

        <table class="scale">
          <tr class="scale">
            <th class="scale">Quantity</th>
            <th class="scale">Weight</th>
            <th class="scale">Weight per piece</th>
            <th class="scale" />
          </tr>
          <tr class="scale">
            <td class="scale">1</td>
            <td class="scale">{{ readings[1] }}</td>
            <td class="scale">{{ readings[1] }}</td>
            <td class="scale"><el-button type="primary" @click="readScale(1)">Read</el-button></td>
          </tr>
          <tr class="scale">
            <td class="scale">5</td>
            <td class="scale">{{ readings[5] }}</td>
            <td class="scale">{{ readings[5]/5 }}</td>
            <td class="scale"><el-button type="primary" @click="readScale(5)">Read</el-button></td>
          </tr>
          <tr class="scale">
            <td class="scale">10</td>
            <td class="scale">{{ readings[10] }}</td>
            <td class="scale">{{ readings[10]/10 }}</td>
            <td class="scale"><el-button type="primary" @click="readScale(10)">Read</el-button></td>
          </tr>
          <tr class="scale">
            <td class="scale">20</td>
            <td class="scale">{{ readings[20] }}</td>
            <td class="scale">{{ readings[20]/20 }}</td>
            <td class="scale"><el-button type="primary" @click="readScale(20)">Read</el-button></td>
          </tr>
        </table>
        <el-button type="danger" @click="clear()">Clear</el-button>
      </el-form-item>

      <el-form-item label="Weight per piece:">
        {{ weightPerPiece }}
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="readScale()">Read weight</el-button>
      </el-form-item>

      <el-form-item label="Reading:">
        {{ reading }}
      </el-form-item>

      <el-form-item label="Quantity:">
        {{ quantity }}
      </el-form-item>

    </el-form>

  </div>
</template>

<script>

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

export default {
  components: {},
  props: {

  },
  data() {
    return {
      scales: [],
      selectedScaleId: null,
      reading: 0,
      readings: [],
      weightPerPiece: 0,
      quantity: 0

    }
  },
  async mounted() {
    this.getScales()
  },
  methods: {
    getScales() {
      peripheral.search('scale').then(response => {
        this.scales = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    clear() {
      this.readings = []
      this.$forceUpdate()
    },
    readScale(quantity) {
      peripheral.scale.read(this.selectedScaleId).then(response => {
        if (quantity === undefined) {
          this.reading = response.value
          this.quantity = this.reading / this.weightPerPiece
        } else {
          this.readings[quantity] = response.value

          this.weightPerPiece = 0
          let numberOfItems = 0
          if (this.readings[1] !== undefined) {
            this.weightPerPiece += this.readings[1]
            numberOfItems++
          }
          if (this.readings[5] !== undefined) {
            this.weightPerPiece += this.readings[5] / 5
            numberOfItems++
          }
          if (this.readings[10] !== undefined) {
            this.weightPerPiece += this.readings[10] / 10
            numberOfItems++
          }
          if (this.readings[20] !== undefined) {
            this.weightPerPiece += this.readings[20] / 20
            numberOfItems++
          }
          this.weightPerPiece = this.weightPerPiece / numberOfItems
        }
        this.$forceUpdate()
        console.log(this.readings)
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

<style>
table.scale, th.scale, tr.scale, td.scale {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>

