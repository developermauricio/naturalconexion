!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=12)}([function(e,t){e.exports=window.wp.element},function(e,t){e.exports=window.wc.blocksCheckout},function(e,t){e.exports=window.wp.i18n},function(e){e.exports=JSON.parse('{"apiVersion":2,"name":"woocommerce/mailchimp-newsletter-subscription","version":"1.0.0","title":"Mailchimp Newsletter!","category":"woocommerce","description":"Adds a newsletter subscription checkbox to the checkout.","supports":{"html":true,"align":false,"multiple":false,"reusable":false},"parent":["woocommerce/checkout-contact-information-block"],"attributes":{"lock":{"type":"object","default":{"remove":true,"move":true}}},"textdomain":"mailchimp-woocommerce","editorStyle":"file:../../../build/style-newsletter-block.css"}')},,function(e,t,n){"use strict";var r=n(6);const{optinDefaultText:o,gdprHeadline:i,gdprFields:c}=Object(r.getSetting)("mailchimp-newsletter_data","");t.a={text:{type:"string",default:o},gdprHeadline:{type:"string",default:i},gdpr:{type:"array",default:c}}},function(e,t){e.exports=window.wc.wcSettings},,,function(e,t){e.exports=window.wc.wcBlocksSharedHocs},,,function(e,t,n){"use strict";n.r(t);var r=n(1),o=n(9),i=n(0),c=n(2),a=n(5),l=n(3);Object(r.registerCheckoutBlock)({metadata:l,component:Object(o.withFilteredAttributes)(a.a)(({cart:e,extensions:t,text:n,gdprHeadline:o,gdpr:a,checkoutExtensionData:l})=>{let s={};a&&a.length&&a.forEach(e=>{s[e.marketing_permission_id]=!1});const[u,p]=Object(i.useState)(!1),[d]=Object(i.useState)({}),{setExtensionData:m}=l;return Object(i.useEffect)(()=>{m("mailchimp-newsletter","optin",u)},[u,m]),Object(i.createElement)(i.Fragment,null,Object(i.createElement)(r.CheckboxControl,{id:"subscribe-to-newsletter",checked:u,onChange:p},Object(i.createElement)("span",{dangerouslySetInnerHTML:{__html:n}})),a&&a.length?Object(c.__)(o,"mailchimp-for-woocommerce"):"",a&&a.length?a.map(e=>Object(i.createElement)(r.CheckboxControl,{id:"gdpr_"+e.marketing_permission_id,checked:d[e.marketing_permission_id],onChange:t=>{d[e.marketing_permission_id]=!d[e.marketing_permission_id],m("mailchimp-newsletter","gdprFields",d)}},Object(i.createElement)("span",{dangerouslySetInnerHTML:{__html:e.text}}))):"")})})}]);