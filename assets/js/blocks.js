(function (blocks, i18n, element, components) {
    const { registerBlockType } = blocks;
    const { __ } = i18n;
    const { createElement: el, Fragment } = element;
    const { Placeholder, TextControl } = components;

    registerBlockType('worldquest/world-quests-list', {
        edit: function () {
            return el(Placeholder, { label: __('World Quests List', 'worldquest') }, __('Rendered on frontend.', 'worldquest'));
        },
        save: function () { return null; }
    });

    registerBlockType('worldquest/world-quest-viewer', {
        edit: function (props) {
            return el(Fragment, {},
                el(Placeholder, { label: __('World Quest Viewer', 'worldquest') },
                    el(TextControl, {
                        label: __('Quest ID', 'worldquest'),
                        type: 'number',
                        value: props.attributes.questId || 0,
                        onChange: function (value) {
                            props.setAttributes({ questId: parseInt(value || '0', 10) || 0 });
                        }
                    })
                )
            );
        },
        save: function () { return null; }
    });
})(window.wp.blocks, window.wp.i18n, window.wp.element, window.wp.components);
