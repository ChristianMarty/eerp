<template>
  <div class="app-container">
    <el-form ref="postForm" :model="postForm" class="form-container">
      <el-steps :active="active" finish-status="success">
        <el-step title="Part" />
        <el-step title="Attributes" />
        <el-step title="Documents" />
        <el-step title="Suppliers" />
        <el-step title="Finish" />
      </el-steps>
      <div v-if="active == 0">
        <el-form-item style="margin-bottom: 40px;" prop="title">
          <MDinput v-model="postForm.mpn" :maxlength="100" name="name" required>
            Manufacturer Part Number
          </MDinput>
        </el-form-item>
        <el-row>
          <el-form-item label="Manufacturer:">
            <el-select v-model="postForm.mfr" filterable>
              <el-option
                v-for="item in manufacturers"
                :key="item.Name"
                :label="item.Name"
                :value="item.Name"
              />
            </el-select>
          </el-form-item>
        </el-row>
        <el-form-item label="Part Class:">
          <el-cascader-panel
            v-model="postForm.class"
            :options="partClasses"
            :props="{emitPath: false, value:'Name', label: 'Name', children:'Children', disabled: 'NoParts', checkStrictly: true}"
          />
        </el-form-item>
      </div>
      <div v-if="active == 1">
        <el-table :data="attributes">
          <el-table-column
            label="Name"
            prop="Name"
          />
          <el-table-column label="Min">
            <template v-if="props.row.MinMax == true" slot-scope="props">
              <el-input />
            </template>
          </el-table-column>
          <el-table-column label="Typ">
            <template slot-scope="props">
              <el-input />
            </template>
          </el-table-column>
          <el-table-column label="Max">
            <template v-if="props.row.MinMax == true" slot-scope="props">
              <el-input />
            </template>
          </el-table-column>
          <el-table-column
            label="Unit"
            prop="Unit"
          />
        </el-table></div>
    </el-form>
    <el-button style="margin-top: 12px;" @click="next">Next</el-button>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import MDinput from '@/components/MDinput'

const partAttribute = {
  name: '',
  vlaue: ''
}

const defaultForm = {
  mpn: '',
  mfr: '',
  class: null
}

export default {
  components: { MDinput },
  data() {
    return {
      postForm: Object.assign({}, defaultForm),
      active: 0,
      input: null,
      manufacturers: null,
      partClasses: null,
      attributes: null
    }
  },
  mounted() {
    this.getManufacturers()
    this.getPartClasses()
  },
  methods: {

    next() {
      if (this.active++ > 4) this.active = 0
      if (this.active === 1) this.getAttributes(this.postForm.class)
    },
    getManufacturers() {
      requestBN({
        url: '/manufacturers/',
        methood: 'get'
      }).then(response => {
        this.manufacturers = response.data
      })
    },
    getAttributes(partClassName) {
      requestBN({
        url: '/partClassAttributes/',
        methood: 'get',
        params: { class: partClassName }
      }).then(response => {
        this.attributes = response.data
      })
    },
    getPartClasses() {
      requestBN({
        url: '/partClasses/',
        methood: 'get'
      }).then(response => {
        this.partClasses = response.data
      })
    }
  }
}
</script>
