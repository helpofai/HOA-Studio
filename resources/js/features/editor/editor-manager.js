/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

/**
 * HOA-Studio Multi-Type Editor Manager
 * Strategy pattern dispatcher supporting Tiptap, Gutenberg, Block Editor, etc.
 */
class HoaEditorManager {
    constructor() {
        this.drivers = new Map();
        this.instances = new Map();
    }

    registerDriver(type, driverClass) {
        this.drivers.set(type, driverClass);
    }

    createEditor(type, elementId, config = {}) {
        if (!this.drivers.has(type)) {
            console.warn(`[HOA Editor] Driver "${type}" not registered. Falling back to "tiptap".`);
            type = 'tiptap';
        }

        const DriverClass = this.drivers.get(type);
        if (!DriverClass) {
            console.error(`[HOA Editor] No driver found for type: ${type}`);
            return null;
        }

        const instance = new DriverClass(elementId, config);
        this.instances.set(elementId, instance);
        return instance;
    }

    getInstance(elementId) {
        return this.instances.get(elementId);
    }

    destroyInstance(elementId) {
        const instance = this.instances.get(elementId);
        if (instance && typeof instance.destroy === 'function') {
            instance.destroy();
        }
        this.instances.delete(elementId);
    }
}

window.HOA_EditorManager = new HoaEditorManager();