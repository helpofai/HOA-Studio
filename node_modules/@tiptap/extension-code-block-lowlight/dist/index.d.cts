import { CodeBlockOptions } from "@tiptap/extension-code-block";
//#region src/code-block-lowlight.d.ts
interface CodeBlockLowlightOptions extends CodeBlockOptions {
  /**
   * The lowlight instance.
   */
  lowlight: any;
}
/**
 * This extension allows you to highlight code blocks with lowlight.
 * @see https://tiptap.dev/api/nodes/code-block-lowlight
 */
declare const CodeBlockLowlight: import("@tiptap/core").Node<CodeBlockLowlightOptions, any>;
//#endregion
export { CodeBlockLowlight, CodeBlockLowlight as default, CodeBlockLowlightOptions };
//# sourceMappingURL=index.d.cts.map