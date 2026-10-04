import Alpine from 'alpinejs';
import { kasse } from './kasse';
import { scanner } from './scanner';

window.Alpine = Alpine;
Alpine.data('kasse', kasse);
Alpine.data('scanner', scanner);
Alpine.start();
