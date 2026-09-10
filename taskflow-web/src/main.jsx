import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import './index.css'
import App from './App.jsx'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // Retry sekali saja (tidak retry kalau 403/404)
      retry: (failureCount, error) => {
        const status = error?.response?.status
        if (status === 401 || status === 403 || status === 404) return false
        return failureCount < 1
      },
      // Data dianggap stale setelah 30 detik
      staleTime: 30_000,
    },
  },
})

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <App />
    </QueryClientProvider>
  </StrictMode>,
)
