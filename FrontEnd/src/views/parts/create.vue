<template>
  <div class="app-container">
    <h1>Create Manufacturer Part</h1>
    <el-divider />

    <el-form label-width="150px">

      <el-form-item label="Manufacturer:">
        <el-select v-model="form.ManufacturerName" filterable>
          <el-option
            v-for="item in manufacturers"
            :key="item.Name"
            :label="item.Name"
            :value="item.Name"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Part Number:">
        <el-input v-model="form.ManufacturerPartNumber" />
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="save">Save</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const defaultForm = {
  ManufacturerName: '',
  ManufacturerPartNumber: ''
}

export default {
  components: { },
  data() {
    return {
      form: Object.assign({}, defaultForm),
      manufacturers: null
    }
  },
  mounted() {
    this.getManufacturers()
  },
  methods: {

    getManufacturers() {
      requestBN({
        url: '/part/manufacturer/',
        methood: 'get'
      }).then(response => {
        this.manufacturers = response.data
      })
    },
    save() {
      requestBN({
        method: 'post',
        url: '/part',
        data: { data: this.form }
      }).then(response => {
        if (response.error !== null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.$router.push('/mfrParts/partView/' + response.data.ManufacturerPartId)
        }
      })
    }
  }
}
</script>
