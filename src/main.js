import { createApp } from 'vue'
import '@nextcloud/dialogs/style.css'
import { showError } from '@nextcloud/dialogs'
import App from './App.vue'
import { installErrorInterceptor } from './utils/apiErrors.js'

installErrorInterceptor()

const app = createApp(App)

app.config.errorHandler = (err) => {
  console.error('[Crate] Unhandled error:', err)
  showError('An unexpected error occurred.')
}

app.mount('#app')
