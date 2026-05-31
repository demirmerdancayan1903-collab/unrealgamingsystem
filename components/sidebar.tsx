'use client'

import { categories } from '@/lib/games'
import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'

interface SidebarProps {
  selectedCategory: string
  setSelectedCategory: (category: string) => void
}

export function Sidebar({ selectedCategory, setSelectedCategory }: SidebarProps) {
  return (
    <aside className="hidden lg:block w-56 shrink-0">
      <div className="sticky top-20">
        <h2 className="text-sm font-semibold text-muted-foreground uppercase tracking-wider mb-3 px-2">
          Kategoriler
        </h2>
        <ScrollArea className="h-[calc(100vh-140px)]">
          <div className="flex flex-col gap-1 pr-4">
            {categories.map((category) => (
              <Button
                key={category.id}
                variant={selectedCategory === category.id ? 'default' : 'ghost'}
                className={`justify-start h-10 ${
                  selectedCategory === category.id 
                    ? 'bg-primary text-primary-foreground' 
                    : 'hover:bg-secondary'
                }`}
                onClick={() => setSelectedCategory(category.id)}
              >
                <span className="text-lg mr-2">{category.icon}</span>
                <span className="text-sm">{category.name}</span>
              </Button>
            ))}
          </div>
        </ScrollArea>
      </div>
    </aside>
  )
}
