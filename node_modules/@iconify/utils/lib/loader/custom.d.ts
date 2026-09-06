import { CustomIconLoader, IconifyLoaderOptions, InlineCollection } from "./types.js";
/**
 * Get custom icon from inline collection or using loader
 */
export declare function getCustomIcon(custom: CustomIconLoader | InlineCollection, collection: string, icon: string, options?: IconifyLoaderOptions): Promise<string | undefined>;