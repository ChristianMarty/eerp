<template>
  <div class="home">
    <div class="header">
      <img class="header" src="/src/assets/logo.png" alt="Logo">
    </div>
    <div class="wrapper">
      <router-link to="/removeStock">
        <div class="box">
          <h2>Remove Stock</h2>
        </div>
      </router-link>
      <router-link to="/countStock">
        <div class="box">
          <h2>Count Stock</h2>
        </div>
      </router-link>
      <router-link to="/countStock">
        <div class="box">
          <h2>Location Transfer</h2>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script lang="ts">

export default {
  name: 'HomeView',
  components: {
  }
}
</script>

<style>

body {
	background-color: rgb(156, 154, 154);
	margin: 0;
}

div.header {
	background-color: rgb(255, 255, 255);
	width: 100%;
	margin: 0;
	margin-bottom: 20px;
	padding: 15px;
	position: fixed;
  top: 0;
}


.wrapper {
	padding-top: 370px;
	padding-bottom: 500px;
	margin-left: 20px;
	margin-right: 20px;
	grid-gap: 20px;

  display: grid;
  place-items: center;
	justify-content: center;
	align-content: center;

  grid-template-columns: repeat(auto-fill, 25%);
}

.box {
	background-color: #5f5e5e;
	color: #fff;
	border-radius: 5px;
	padding: 10px;
	text-align: center;
	height: 200px;
	width: 200px;
}
</style>
