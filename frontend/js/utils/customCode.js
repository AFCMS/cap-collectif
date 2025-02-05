// @flow

const getNodes = str => new window.DOMParser().parseFromString(str, 'text/html').head.childNodes;

export const insertCustomCode = (code?: ?string) => {
  let customCode = document.getElementById('capcoCustomCodeWrapper');

  if (!customCode) {
    customCode = document.createElement('div');
    customCode.setAttribute('id', 'capcoCustomCodeWrapper');
    if (document.body) document.body.appendChild(customCode);
  }
  customCode.innerHTML = '';

  if (code) {
    const nodes = getNodes(code);
    for (const node of nodes) {
      if (node.nodeType === 1) {
        // eslint-disable-next-line no-eval
        if (node.tagName === 'SCRIPT') window.eval(node.innerText);
        else if (node.tagName === 'STYLE') {
          customCode.appendChild(node);
        }
      }
    }
  }
};
