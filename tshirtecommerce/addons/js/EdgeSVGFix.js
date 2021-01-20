// This is a combination of an SVG import function from http://stackoverflow.com/users/405017/phrogz
// and a posting by Daniel McCabe https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/5579311/
// It allows an SVG object (obtained for example, from an AJAX request) to be correctly displayed in the
// Microsoft Edge browser (which otherwise would change some of the attribute names to all Upper Case)
// For example, I call this function if (navigator.userAgent.indexOf('Edge') > 0)
function cloneAndFix(node, doc) {
    var corrections = new Array (
            'attributeName',
            'attributeType',
            'baseFrequency',
            'baseProfile',
            'calcMode',
            'clipPathUnits',
            'contentScriptType',
            'contentStyleType',
            'diffuseConstant',
            'edgeMode',
            'externalResourcesRequired',
            'filterRes',
            'filterUnits',
            'glyphRef',
            'gradientTransform',
            'gradientUnits',
            'kernelMatrix',
            'kernelUnitLength',
            'keyPoints',
            'keySplines',
            'keyTimes',
            'lengthAdjust',
            'limitingConeAngle',
            'markerHeight',
            'markerUnits',
            'markerWidth',
            'maskContentUnits',
            'maskUnits',
            'numOctaves',
            'pathLength',
            'patternContentUnits',
            'patternTransform',
            'patternUnits',
            'pointsAtX',
            'pointsAtY',
            'pointsAtZ',
            'preserveAlpha',
            'preserveAspectRatio',
            'primitiveUnits',
            'refX',
            'refY',
            'repeatCount',
            'repeatDur',
            'requiredExtensions',
            'requiredFeatures',
            'specularConstant',
            'specularExponent',
            'spreadMethod',
            'startOffset',
            'stdDeviation',
            'stitchTiles',
            'surfaceScale',
            'systemLanguage',
            'tableValues',
            'targetX',
            'targetY',
            'textLength',
            'viewBox',
            'viewTarget',
            'xChannelSelector',
            'yChannelSelector',
            'zoomAndPan'
            );
  if (!doc) doc = document;
  var clone = doc.createElementNS(node.namespaceURI,node.nodeName);
  for (var i = 0,len = node.attributes.length; i < len; ++i) {
    var a = node.attributes[i];
    if (/^xmlns\b/.test(a.nodeName)) continue; // IE can't create these
    var validName = a.localName.toLowerCase(); // Make a first guess that the attribute name should be lower case
    for (var j = 0, len2 = corrections.length; j < len2; j++) { // but if we find it in our list of camelCase names...
        if (validName === corrections[j].toLowerCase()) { // then use that version instead
            validName = corrections[j];
            break;
        }
    }
    clone.setAttributeNS(a.namespaceURI,validName,a.nodeValue);
  }
  for (var i = 0, len = node.childNodes.length; i < len; ++i){
    var c = node.childNodes[i];
    clone.insertBefore(
      c.nodeType ===1 ? cloneAndFix(c,doc) : doc.createTextNode(c.nodeValue),
      null
    ); }
  return clone;
}
