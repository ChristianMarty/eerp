<template>
  <div
    v-loading="loading"
    element-loading-text="Loading..."
    element-loading-spinner="el-icon-loading"
    class="base-class-search-container"
  >
    <el-card
      v-for="item in data"
      :key="item.Id"
      shadow="hover"
      class="small-box"
      @click.native="onClassSelect(item.Id)"
    >
      <div slot="header" class="clearfix">
        <span class="headerClass">{{ item.Name }}</span>
        <!--<el-image style="float: right; padding: 3px 0; height: 50px;width: 80px;" :src="item.PicturePath" :fit="scale-down" />-->
      </div>
      <div v-for="subItem in item.Children" :key="subItem.Id" class="text item">
        <span> {{ subItem.Name }} </span>
      </div>
    </el-card>
    <el-card
      shadow="hover"
      class="small-box"
      @click.native="onClassSelect(null)"
    >
      <div slot="header" class="clearfix">
        <span class="headerClass">Undefined</span>
      </div>
      <div class="text item">
        <span>Parts not assigned to a class</span>
      </div>
    </el-card>
  </div>
</template>


<script>
import Part from '@/api/part'
const part = new Part()

export default {
  name: 'BaseClassSearchContainer',
  data() {
    return {
      loading: true,
      data: []
    }
  },
  created() {
  },
  mounted() {
    this.getManufacturerPartClass()
  },
  methods: {
    onClassSelect(ClassId = 0) {
      if (ClassId === 0) this.$router.push({ query: { }})
      else this.$router.push({ query: { ClassId: String(ClassId) }})
    },
    getManufacturerPartClass() {
      part.class.list(0).then(response => {
        this.data = response
        this.loading = false
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
  .small-box {
    float: left;
    width: 330px;
    height: 330px;
    margin: 10px;
    cursor: pointer;
  }
  .headerClass {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
  }
  .clearfix:before,
  .clearfix:after {
    display: table;
    content: "";
    text-align: center;
  }
  .clearfix:after {
    clear: both
  }
</style>
