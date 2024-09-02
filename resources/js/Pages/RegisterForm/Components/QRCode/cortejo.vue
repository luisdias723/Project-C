<script setup>
import { ref } from 'vue';

// Import the qrcode to use
import QRCodeVue3 from "qrcode-vue3";

// Getting the props
const props = defineProps(['message'])

// setting the emmit
const emit = defineEmits(['goBack']);

// Variables to generate the qrcode
let width = ref(200);
let height =ref(200);
let value = ref("{id: 1, name: 'Luis', apelido: 'Dias', type: 'Mordomia'}");
let qrOptions = ref({ typeNumber: 0, mode: 'Byte', errorCorrectionLevel: 'H' });
let imageOptions = ref({ hideBackgroundDots: true, imageSize: 0.4, margin: 0 });
let dotsOptions = ref({
            type: 'extra-rounded',
            color: '#6a1a4c',
            gradient: {
              type: 'linear',
              rotation: 0,
              colorStops: [
                { offset: 0, color: '#ab0812' },
                { offset: 1, color: '#ab0812' },
              ],
            },
          });
let backgroundOptions = ref({ color: '#ffffff' });
let cornersSquareOptions = ref({ type: 'extra-rounded', color: '#ab0812' });
let cornersDotOptions = ref({ type: undefined, color: '#ab0812' });
let dotsOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#6a1a4c',color2:'#6a1a4c',rotation:'0'}});
let cornersSquareOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#000000',color2:'#000000',rotation:'0'}});
let cornersDotOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#000000',color2:'#000000',rotation:'0'}});
let backgroundOptionsHelper = ref({colorType:{single:true,gradient:false},gradient:{linear:true,radial:false,color1:'#000000',color2:'#000000',rotation:'0'}});
let fileExt = ref("png");
let download = ref(true);
let image = ref('uploads/viana-festas-logo.png');


// Methods/Functions
const goBack = () => {
    emit('goBack');
}

</script>

<template>
    <p>{{ props.message }}</p>

    <QRCodeVue3
        :width="width"
        :height="height"
        :value="value"
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