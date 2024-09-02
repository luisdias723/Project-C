import axios from 'axios';
import { ElMessage } from 'element-plus';
import { getToken, setToken } from '@/utils/auth';
import store from '@/store';

// Create axios instance
const service = axios.create({
  baseURL: process.env.MIX_BASE_API,
});

// Request intercepter
service.interceptors.request.use(
  config => {
    const token = getToken();
    if (token) {
      config.headers['Authorization'] = 'Bearer ' + getToken(); // Set JWT token
    }
    if (config.data instanceof FormData) {
      if (config.data.has('file_uploading')) {
        config.timeout = 0;
        config.onUploadProgress = progressEvent => store.dispatch('setProgress', Math.round((progressEvent.loaded * 100) / progressEvent.total));
      }
      if (config.data.has('file_downloading')) {
        const file_size = config.data.get('file_size');
        config.timeout = 0;
        config.onDownloadProgress = progressEvent => store.dispatch('setProgress', Math.round((progressEvent.loaded * 100) / file_size));
      }
    } else {
      config.timeout = 60000;
    }
    return config;
  },
  error => {
    // Do something with request error
    console.log(error); // for debug
    Promise.reject(error);
  }
);

// response pre-processing
service.interceptors.response.use(
  response => {
    if (response.headers.authorization) {
      setToken(response.headers.authorization);
      response.data.token = response.headers.authorization;
    }

    return response.data;
  },
  error => {
    let message = error.message;
    if(error.response.status==403 && error.response.data && !error.response.data.errors && !error.response.data.error){
      message = 'Não possui permissões para realizar esta operação';
    }
    if (error.response.data && error.response.data.errors) {
      message = error.response.data.errors;
    } else if (error.response.data && error.response.data.error) {
      message = error.response.data.error;
    }



    ElMessage({
      message: message,
      type: 'error',
      duration: 5 * 1000,
    });
    return Promise.reject(error);
  },
);

export default service;
