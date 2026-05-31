'use client'

import { Gamepad2, Mail, Twitter, Youtube, Instagram } from 'lucide-react'
import Link from 'next/link'
import { categories } from '@/lib/games'

export function Footer() {
  return (
    <footer className="bg-card border-t border-border mt-12">
      <div className="max-w-[1800px] mx-auto px-4 py-12">
        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
          {/* Brand */}
          <div className="col-span-2 md:col-span-1 lg:col-span-2">
            <Link href="/" className="flex items-center gap-2 mb-4">
              <div className="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                <Gamepad2 className="w-6 h-6 text-primary-foreground" />
              </div>
              <div>
                <h1 className="text-lg font-bold text-foreground leading-tight">Unreal Gaming</h1>
                <p className="text-xs text-muted-foreground -mt-0.5">System</p>
              </div>
            </Link>
            <p className="text-sm text-muted-foreground mb-4 max-w-xs">
              Binlerce ücretsiz online oyunun keyfini çıkarın. Her yaş ve zevke uygun oyunlar burada!
            </p>
            <div className="flex gap-3">
              <Link href="#" className="w-9 h-9 rounded-lg bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors">
                <Twitter className="w-4 h-4" />
              </Link>
              <Link href="#" className="w-9 h-9 rounded-lg bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors">
                <Youtube className="w-4 h-4" />
              </Link>
              <Link href="#" className="w-9 h-9 rounded-lg bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors">
                <Instagram className="w-4 h-4" />
              </Link>
              <Link href="#" className="w-9 h-9 rounded-lg bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors">
                <Mail className="w-4 h-4" />
              </Link>
            </div>
          </div>

          {/* Categories */}
          <div>
            <h3 className="font-semibold text-foreground mb-4">Kategoriler</h3>
            <ul className="space-y-2">
              {categories.slice(1, 8).map((cat) => (
                <li key={cat.id}>
                  <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                    {cat.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* More Categories */}
          <div>
            <h3 className="font-semibold text-foreground mb-4">Daha Fazla</h3>
            <ul className="space-y-2">
              {categories.slice(8).map((cat) => (
                <li key={cat.id}>
                  <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                    {cat.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Links */}
          <div>
            <h3 className="font-semibold text-foreground mb-4">Bağlantılar</h3>
            <ul className="space-y-2">
              <li>
                <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                  Hakkımızda
                </Link>
              </li>
              <li>
                <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                  İletişim
                </Link>
              </li>
              <li>
                <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                  Gizlilik Politikası
                </Link>
              </li>
              <li>
                <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                  Kullanım Şartları
                </Link>
              </li>
              <li>
                <Link href="#" className="text-sm text-muted-foreground hover:text-primary transition-colors">
                  Oyun Ekle
                </Link>
              </li>
            </ul>
          </div>
        </div>

        <div className="mt-12 pt-8 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-sm text-muted-foreground">
            © 2024 Unreal Gaming System. Tüm hakları saklıdır.
          </p>
          <p className="text-sm text-muted-foreground">
            unrealgamingsystem.com
          </p>
        </div>
      </div>
    </footer>
  )
}
