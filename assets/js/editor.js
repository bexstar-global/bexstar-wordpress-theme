/* global wp */
(function () {
    'use strict';
    const el = wp.element.createElement;
    const { __ } = wp.i18n;
    const ServerSideRender = wp.serverSideRender;
    wp.blocks.registerBlockType('bexstar/navigation', {
        apiVersion: 3, title: __('BEXSTAR navigation', 'bexstar'), category: 'theme', icon: 'menu',
        supports: {html: false, multiple: false},
        edit: function () {
            return el('div', wp.blockEditor.useBlockProps(),
                el(wp.components.Notice, {status: 'info', isDismissible: false},
                    __('Navigation uses the BEXSTAR primary menu, or the planned V3 hierarchy until a menu is assigned. See docs/editor-guide.md.', 'bexstar')),
                el('div', {inert: ''}, el(ServerSideRender, {block: 'bexstar/navigation'})));
        },
        save: function () { return null; }
    });
    const slots = ['hero','sea-freight','air-freight','rail-truck','express','usa','europe','uk','canada','australia','middle-east','sourcing','supply-chain','fba','fba-video','port','ecommerce','amazon-sellers','importers','wholesalers','retailers','manufacturers','case-study','final-cta'];
    wp.blocks.registerBlockType('bexstar/media', {
        apiVersion: 3, title: __('BEXSTAR media slot', 'bexstar'), category: 'media', icon: 'format-image',
        supports: {html: false},
        attributes: {
            slot: {type:'string', default:'hero'}, imageId: {type:'integer', default:0},
            videoId: {type:'integer', default:0}, alt: {type:'string', default:''},
            decorative: {type:'boolean', default:true}, position: {type:'string', default:'50% 50%'},
            mobilePosition: {type:'string', default:'50% 50%'}
        },
        edit: function (props) {
            const a = props.attributes;
            function upload(kind) {
                const field = kind === 'image' ? 'imageId' : 'videoId';
                return el(wp.blockEditor.MediaUploadCheck, {},
                    el(wp.blockEditor.MediaUpload, {
                        allowedTypes: kind === 'image' ? ['image'] : ['video/mp4'],
                        value: a[field],
                        onSelect: function (file) { props.setAttributes({[field]: file.id}); },
                        render: function (control) {
                            return el(wp.components.Button, {variant: 'secondary', onClick: control.open},
                                kind === 'image' ? __('Choose image / poster', 'bexstar') : __('Choose decorative MP4', 'bexstar'));
                        }
                    }));
            }
            return el(wp.element.Fragment, {},
                el(wp.blockEditor.InspectorControls, {},
                    el(wp.components.PanelBody, {title: __('Media slot', 'bexstar')},
                        el(wp.components.SelectControl, {label: __('Placement', 'bexstar'), value: a.slot,
                            options: slots.map(function (v) { return {label:v, value:v}; }),
                            onChange: function (slot) {props.setAttributes({slot});}}),
                        upload('image'), upload('video'),
                        el(wp.components.Button, {variant:'tertiary', onClick:function(){props.setAttributes({imageId:0,videoId:0});}}, __('Use theme asset / fallback', 'bexstar')),
                        el(wp.components.ToggleControl, {label:__('Decorative image', 'bexstar'), checked:a.decorative,
                            onChange:function(decorative){props.setAttributes({decorative});}}),
                        !a.decorative && el(wp.components.TextControl, {label:__('Alternative text', 'bexstar'), value:a.alt,onChange:function(alt){props.setAttributes({alt});}}),
                        el(wp.components.TextControl, {label:__('Desktop focal point, e.g. 50% 50%', 'bexstar'),value:a.position,onChange:function(position){props.setAttributes({position});}}),
                        el(wp.components.TextControl, {label:__('Mobile focal point, e.g. 50% 50%', 'bexstar'),value:a.mobilePosition,onChange:function(mobilePosition){props.setAttributes({mobilePosition});}})
                    )),
                el('div', wp.blockEditor.useBlockProps(), el(ServerSideRender, {block:'bexstar/media', attributes:a})));
        },
        save: function () { return null; }
    });
}());
