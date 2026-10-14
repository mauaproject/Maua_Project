import { createContext, useContext } from 'react'

const BlogRuntime = createContext({
  hrefFor: (path) => path,
  session: null,
  logout: () => {},
  uploadImage: async () => { throw new Error('Upload gambar belum tersedia.') },
})

export const BlogRuntimeProvider = BlogRuntime.Provider
export const useBlogRuntime = () => useContext(BlogRuntime)
