<template>
  <div class="upload-container">
    <el-form ref="postForm" :model="postForm" class="form-container">
      <el-form-item label="Document Description:">
        <el-input v-model="postForm.Description" />
      </el-form-item>

      <el-form-item label="Document Type:">
        <el-select v-model="postForm.Type" filterable>
          <el-option
            v-for="item in documentTypeOptions"
            :key="item"
            :label="item"
            :value="item"
          />
        </el-select>
      </el-form-item>

      <el-form-item label="Document:">
        <el-upload
          ref="upload"
          :action="docUrl"
          :data="postForm"
          drag
          :auto-upload="false"
          :name="Document"
          limit="1"
          :on-success="onUploadSuccess"
          :on-change="handlechange"
          :on-error="onUploadError"
        >
          <i class="el-icon-upload" />
          <div class="el-upload__text">
            Drop file here or <em>click to upload</em>
          </div>
        </el-upload>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="onSubmit">Create</el-button>
        <el-button type="danger" @click="resetForm">Clear</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
/*
<el-aside width="100%">Upload document OR specify Document Link</el-aside>
   <el-form-item label="Document Link:">
        <el-input v-model="postForm.Link" />
      </el-form-item>
*/
import Document from '@/api/document'
const document = new Document()

const formData = {
  Description: null,
  Type: null,
  Link: null,
  Document: null
}

export default {
  components: {},
  props: {
    value: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      postForm: Object.assign({}, formData),
      tempUrl: '',
      dataObj: { token: '', key: '' },
      fileList: null,
      documentTypeOptions: null,
      documentType: null,
      result: null,
      docUrl: process.env.VUE_APP_BLUENOVA_API + '/document/item',
      resp: null
    }
  },
  computed: {
    imageUrl() {
      return this.value
    }
  },
  async mounted() {
    this.documentTypeOptions = await document.types()
  },
  methods: {
    onSubmit() {
      this.creatDocument()
    },
    resetForm() {
      this.postForm.Type = null
      this.postForm.Link = null
      this.postForm.Document = null
      this.postForm.Description = null

      this.$refs.upload.clearFiles()
    },
    handlechange(file, fileList) {
      if (this.postForm.Description === null) {
        this.postForm.Description = file.name
          .split('.')
          .slice(0, -1)
          .join('.')
          .replaceAll('_', ' ')
      }
    },
    onUploadSuccess(response, file, fileList) {
      this.resp = response.data
      if (response.error === null) {
        this.$message({
          showClose: true,
          message: this.resp.message,
          type: 'success'
        })
        this.resetForm()
      } else {
        this.$message({
          showClose: true,
          duration: 0,
          message: response.error,
          type: 'error'
        })
      }
    },
    onUploadError(err, file, fileList) {
      this.$message({
        showClose: true,
        duration: 0,
        message: err,
        type: 'error'
      })
    },
    creatDocument() {
      this.$refs.upload.submit()
    }
  }
}
</script>
