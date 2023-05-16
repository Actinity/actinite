window._ = require('lodash');

window.axios = require('axios');
window.jsonLogic = require('json-logic-js');

import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import mitt from 'mitt';

import store from './store/index.js';
import routes from './routes.js';

const router = createRouter({
	history: createWebHistory(),
	routes
});

let app = createApp({})
	.use(store)
	.use(router);

app.config.unwrapInjectedRef = true;

window.$mitt = mitt();

app.component('actinite',require('./components/Actinite.vue').default);
//app.component('mailtrapper',require('../../vendor/actinity/mailtrapper/src/components/Mailtrapper.vue').default);

app.config.globalProperties.$mitt = $mitt;

app.config.globalProperties.$filters = {
	fileSize(fileSizeInBytes) {
		let i = -1;
		let byteUnits = ['kB', 'MB', 'GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
		do {
			fileSizeInBytes /= 1024;
			i++;
		} while (fileSizeInBytes > 1024);

		return Math.max(fileSizeInBytes, 0.1).toFixed(i > 0 && fileSizeInBytes !== parseInt(fileSizeInBytes) ? 1 : 0) + byteUnits[i];
	},
	unslug(str) {
		return str.slice(0,1).toUpperCase() + str.slice(1).replaceAll('-',' ');
	}
}

$mitt.on('key:save',() => {
	store.dispatch('Editor/saveIfDirty');
});

document.addEventListener('keydown',(e) => {
	if((e.metaKey || e.ctrlKey) && e.key === 's') {
		e.preventDefault();
		$mitt.emit('key:save');
	}
	if((e.metaKey || e.ctrlKey) && e.key === 'g') {
		e.preventDefault();
		$mitt.emit('key:go');
	}
	if(e.key === 'Escape') {
		$mitt.emit('key:escape');
	}
});

app.mount('#actinite');
