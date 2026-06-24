import bankIndonesiaSvg from 'idn-finlogos/icons/bank-indonesia';
import briSvg from 'idn-finlogos/icons/bri';
import mandiriSvg from 'idn-finlogos/icons/mandiri';
import Google from '@/components/landing/icons/Google.vue';
import Goto from '@/components/landing/icons/Goto.vue';
import Huawei from '@/components/landing/icons/Huawei.vue';
import Ibm from '@/components/landing/icons/Ibm.vue';
import IndosatOoredoHutsicon from '@/components/landing/icons/IndosatOoredoHutsicon.vue';
import TelkomIndonesia from '@/components/landing/icons/TelkomIndonesia.vue';
import { useLogoWrapper } from '@/composables/useLogoWrapper';

const { svgLogoWrapper, idnfinlogosWrapper } = useLogoWrapper();

const google = svgLogoWrapper(Google);
const telkomIndonesia = svgLogoWrapper(TelkomIndonesia);
const bri = idnfinlogosWrapper(briSvg);
const bankIndonesia = idnfinlogosWrapper(bankIndonesiaSvg);
const indosatOoredoHutsicon = svgLogoWrapper(IndosatOoredoHutsicon);
const mandiri = idnfinlogosWrapper(mandiriSvg);
const ibm = svgLogoWrapper(Ibm);
const huawei = svgLogoWrapper(Huawei);
const goto = svgLogoWrapper(Goto);

export {
    google,
    telkomIndonesia,
    bri,
    bankIndonesia,
    indosatOoredoHutsicon,
    mandiri,
    ibm,
    huawei,
    goto,
};
