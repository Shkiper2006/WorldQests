(() => {
  const data = window.WorldQuestViewerData;
  const root = document.querySelector('[data-world-quest-viewer]');
  if (!data || !root) return;

  const content = root.querySelector('[data-node-content]');
  const media = root.querySelector('[data-node-media]');
  const choicesWrap = root.querySelector('[data-node-choices]');
  const nodesByCode = Object.fromEntries(data.nodes.map((n) => [n.node_code, n]));
  const nodesById = Object.fromEntries(data.nodes.map((n) => [String(n.id), n]));
  const choicesByParent = {};

  data.choices.forEach((c) => {
    const key = String(c.parent_node_id);
    choicesByParent[key] = choicesByParent[key] || [];
    choicesByParent[key].push(c);
  });

  const renderNode = (node) => {
    if (!node) return;
    const parser = new DOMParser();
    const doc = parser.parseFromString(node.content || '', 'text/html');
    doc.querySelectorAll('img').forEach((img) => {
      img.loading = 'lazy';
      img.decoding = 'async';
    });

    content.innerHTML = node.content || '';
    media.innerHTML = '';
    const firstImage = doc.querySelector('img');
    if (firstImage) {
      firstImage.loading = 'lazy';
      media.appendChild(firstImage);
    }

    const choices = (choicesByParent[String(node.id)] || []).slice(0, 6);
    choicesWrap.innerHTML = '';
    if (!choices.length) {
      const cta = document.createElement('a');
      cta.href = '#';
      cta.textContent = data.ctaLabel;
      choicesWrap.appendChild(cta);
      return;
    }

    choices.forEach((choice) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = choice.label;
      btn.addEventListener('click', () => {
        const target = nodesByCode[choice.target_node_code] || nodesById[String(choice.target_node_code)];
        renderNode(target);
      });
      choicesWrap.appendChild(btn);
    });
  };

  renderNode(data.nodes[0]);
})();
