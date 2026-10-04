import Alpine from 'alpinejs';
import { kasse } from './kasse';
import { scanner } from './scanner';
import { personenSuche } from './personensuche';
import { pushSchalter } from './push';

window.Alpine = Alpine;
Alpine.data('kasse', kasse);
Alpine.data('scanner', scanner);
Alpine.data('personenSuche', personenSuche);
Alpine.data('pushSchalter', pushSchalter);
Alpine.start();
