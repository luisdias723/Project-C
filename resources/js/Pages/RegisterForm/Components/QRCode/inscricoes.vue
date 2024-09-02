<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';

// Import the qrcode to use
import QRCodeVue3 from "qrcode-vue3";

// Getting the props
const props = defineProps(['message', 'generate', 'text'])

// setting the emmit
const emit = defineEmits(['goBack', 'storeImage']);

// Variables to generate the qrcode
let width = ref(200);
let height =ref(200);
let textToDisplay = ref(props.text);
let qrOptions = ref({ typeNumber: 0, mode: 'Byte', errorCorrectionLevel: 'H' });
let imageOptions = ref({ hideBackgroundDots: true, imageSize: 0.4, margin: 0 });
let dotsOptions = ref({
            type: 'extra-rounded',
            color: '#6a1a4c',
            gradient: {
              type: 'linear',
              rotation: 0,
              colorStops: [
                { offset: 0, color: '#A3679D' },
                { offset: 1, color: '#A3679D' },
              ],
            },
          });
let backgroundOptions = ref({ color: '#ffffff' });
let cornersSquareOptions = ref({ type: 'extra-rounded', color: '#A3679D' });
let cornersDotOptions = ref({ type: undefined, color: '#A3679D' });
let dotsOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#6a1a4c',color2:'#6a1a4c',rotation:'0'}});
let cornersSquareOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#000000',color2:'#000000',rotation:'0'}});
let cornersDotOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#000000',color2:'#000000',rotation:'0'}});
let backgroundOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#000000',color2:'#000000',rotation:'0'}});
let fileExt = ref("png");
let download = ref(false);
let image = ref('uploads/logo-qr.jpg');

const qrCodeRef = ref(null);

const generate = ref(props.generate);

// const save = ref(props.save);

// watch(
//   () => props.save,
//   (newVal, oldVal) => {
//     save.value = newVal;
//     if (save.value == true) {
//       saveQRCode();
//     }
//   }
// );

watch(
  () => props.text,
  (newVal, oldVal) => {
    textToDisplay.value = newVal;
  }
);

const checkQRCodeRendered = async () => {
  await nextTick(); // Wait for DOM updates
  const qrImage = document.querySelector('.img-qr');
  if (qrImage) {
    const imageData = qrImage.src;
    emit('storeImage', imageData);
  } else {
    setTimeout(checkQRCodeRendered, 100); // Check again after 100ms
  }
}

onMounted(() => {
  checkQRCodeRendered();
});

// const saveQRCode = async () => {
//   await nextTick();
//   const qrImage = document.querySelector('.img-qr');
//   console.log(qrImage);
//   if(qrImage) emit('storeImage', qrImage.src);
// }

</script>

<template>
    <QRCodeVue3
        v-if="textToDisplay"
        ref="qrCodeRef"
        :width="width"
        :height="height"
        :value="textToDisplay"
        :qrOptions="qrOptions"
        :imageOptions="imageOptions"
        :dotsOptions="dotsOptions"
        :backgroundOptions="backgroundOptions"
        :cornersSquareOptions="cornersSquareOptions"
        :cornersDotOptions="cornersDotOptions"
        :fileExt="fileExt"
        :download="download"
        :image="image"
        :dotsOptionsHelper="dotsOptionsHelper"
        :cornersSquareOptionsHelper="cornersSquareOptionsHelper"
        :cornersDotOptionsHelper="cornersDotOptionsHelper"
        :backgroundOptionsHelper="backgroundOptionsHelper"
        myclass="my-qur"
        imgclass="img-qr"
        downloadButton="my-button"
        :downloadOptions="{ name: 'vqr', extension: 'png' }"
    />
</template>

<style scoped>
</style>