import { Node, mergeAttributes } from '@tiptap/core';

export const FaqExtension = Node.create({
  name: 'faq',
  group: 'block',
  content: 'faqQuestion faqAnswer',

  parseHTML() {
    return [
      {
        tag: 'details.hoa-faq',
      },
    ];
  },

  renderHTML({ HTMLAttributes }) {
    return ['details', mergeAttributes(this.options.HTMLAttributes, { class: 'hoa-faq' }), 0];
  },
});

export const FaqQuestion = Node.create({
  name: 'faqQuestion',
  group: 'block',
  content: 'text*',
  defining: true,

  parseHTML() {
    return [{ tag: 'summary' }];
  },

  renderHTML({ HTMLAttributes }) {
    return ['summary', mergeAttributes(HTMLAttributes), 0];
  },
});

export const FaqAnswer = Node.create({
  name: 'faqAnswer',
  group: 'block',
  content: 'text*',
  defining: true,

  parseHTML() {
    return [{ tag: 'p' }];
  },

  renderHTML({ HTMLAttributes }) {
    return ['p', mergeAttributes(HTMLAttributes), 0];
  },
});
