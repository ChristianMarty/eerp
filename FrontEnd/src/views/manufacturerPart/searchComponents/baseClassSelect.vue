<template>
  <div
    v-loading="loading"
    element-loading-text="Loading..."
    element-loading-spinner="el-icon-loading"
    class="base-class-search-container"
  >
    <el-card
      v-for="item in data.Children"
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
  </div>
</template>

<script>
export default {
  name: 'BaseClassSearchContainer',
  props: { data: { type: Object, default: null }},
  data() {
    return {
      loading: false
    }
  },
  watch: {
    '$props.data': {
     // if(this.$props.data !== null)
      handler(newVal) {
        if (newVal !== null) this.loading = false
      }
    }
  },
  created() {
  },
  mounted() {
  },
  methods: {
    onClassSelect(ClassId = 0) {
      this.$emit('select', ClassId)
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
