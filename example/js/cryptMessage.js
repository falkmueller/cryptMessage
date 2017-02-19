/*!
 * cryptMessage v1.0.0 (https://falk-m.de)
 * Copyright 2011-2017 falk-m.de
 * Released under an MIT-style license.
 * 
 * Require:
 * base64           - https://github.com/mathiasbynens/base64
 * md5              - https://github.com/blueimp/JavaScript-MD5
 * GibberishAES     - https://github.com/mdp/gibberish-aes/
 * JSEncrypt        - https://github.com/travist/jsencrypt
 */
(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD
    define(['exports'], factory);
  } else if (typeof exports === 'object' && typeof exports.nodeName !== 'string') {
    // Node, CommonJS-like
    factory(module.exports);
  } else {
    factory(root);
  }
})(this, function (exports) {
    
    var cryptMessage = cryptMessage || {};
    
    cryptMessage.helper = cryptMessage.helper || {};
    
    cryptMessage.helper.generateUUID = function(){
        var d = new Date().getTime();
        if(window.performance && typeof window.performance.now === "function"){
            d += performance.now(); //use high-precision timer if available
        }
        var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = (d + Math.random()*16)%16 | 0;
            d = Math.floor(d/16);
            return (c=='x' ? r : (r&0x3|0x8)).toString(16);
        });
        return uuid;
    };
    
    cryptMessage.helper.strtr = function (str, from, to) {
        var subst;
        for (i = 0; i < from.length; i++) {
            subst = (to[i]) ? to[i] : to[to.length-1];
            str = str.replace(new RegExp(str[str.indexOf(from[i])], 'g'), subst);
        }
        return str;
    }
    
    cryptMessage.helper.getTimestamp = function(){
        return Math.round(+new Date()/1000);
    }
    
   cryptMessage.helper.generateRandom = function (len, charSet)
    {
        var text = "";
        len = len || 1;
        charSet = charSet || "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ,;.:-_#+*~!\"§$%&/()=?\\][}{|<>";

        for( var i=0; i < len; i++ ) {
            var randomPoz = Math.floor(Math.random() * charSet.length);
            text +=  charSet.substring(randomPoz,randomPoz+1);
        }
        return text;
    }

    cryptMessage.message = function(){
        if ( !(this instanceof cryptMessage.message) ) {
              return new cryptMessage.message();
        }

        var me = this;

        me.body = new cryptMessage.messageBody();
        me.header = new cryptMessage.messageHeader();
        
        
        me.getCryptMessage = function(public_key){
            var body_key = cryptMessage.helper.generateRandom(32);
            var rawBody = GibberishAES.enc(JSON.stringify(me.body), body_key);
            
            me.header.cryptKey = base64.encode(body_key);
            me.header.digest = md5(rawBody + me.header.slug);
            
            var header_key = cryptMessage.helper.generateRandom(32);
            var rawHeader = GibberishAES.enc(JSON.stringify(me.header), header_key);
            
            var rsaLib = new JSEncrypt({default_key_size: 2048});
            rsaLib.setPublicKey(public_key);
            var rawKey = rsaLib.encrypt(header_key);
            
             if(!rawBody || !rawHeader || !rawKey){
                return null;
            }
            
            return encodeURIComponent(JSON.stringify({"key": rawKey, "header": rawHeader, "body": rawBody}));
        };
        
        me.getFromRawRequest = function(rowMessage, private_key, header_check){
            var rowMessage = JSON.parse(decodeURIComponent(rowMessage));
            
            if(!rowMessage || !rowMessage.header|| !rowMessage.body || ! rowMessage.key){
                return "incorrect rowMessage";
            }
            
            //set Header
            var rsaLib = new JSEncrypt({default_key_size: 2048});
            rsaLib.setPrivateKey(private_key);
            var header_key = rsaLib.decrypt(rowMessage.key);
            
            if(!header_key){
                return "key can not be decrypt";
            }
            
            var header = JSON.parse(GibberishAES.dec(rowMessage.header, header_key));
            if(!header){
                return "header can not be decrypt";
            }
            
            me.header.fromArray(header);
            if(header_check){
                if(!header_check(me.header)){
                    return "invald header";
                }
            }
            
            //set Body
            if(md5(rowMessage.body + me.header.slug) !== me.header.digest){
                return "invald body digest";
            }
            
            var body = JSON.parse(GibberishAES.dec(rowMessage.body, base64.decode(me.header.cryptKey)));
            if(!body){
                return "body can not be decrypt";
            }
            
            me.body.fromArray(body);
            
            return me;
        }
    }

    cryptMessage.messageBody = function(){
        if ( !(this instanceof cryptMessage.messageBody) ) {
              return new cryptMessage.messageBody();
        }

        var me = this;

        me.success = true;
        me.data = null;
        me.message = null;
        me.message_key = null;
        
        me.fromArray = function(o){
            me.success = o.success ? o.success : false;
            me.data = o.data ? o.data : null;
            me.message = o.message ? o.message : null;
            me.message_key = o.message_key ? o.message_key : null;
        }
    }

    cryptMessage.messageHeader = function(){
        if ( !(this instanceof cryptMessage.messageHeader) ) {
              return new cryptMessage.messageHeader();
        }

        var me = this;

        me.timestamp = cryptMessage.helper.getTimestamp();
        me.slug = cryptMessage.helper.generateUUID();
        me.requestSlug = null;
        me.digest = null;
        me.action = null;
        me.cryptKey = null;

        me.fromArray = function(o){
            me.timestamp = o.timestamp ? o.timestamp : 0;
            me.slug = o.slug ? o.slug : "";
            me.requestSlug = o.requestSlug ? o.requestSlug : "";
            me.digest = o.digest ? o.digest : "";
            me.action = o.action ? o.action : "";
            me.cryptKey = o.cryptKey ? o.cryptKey : "";
        }

    }
    
  exports.cryptMessage = cryptMessage;
});



