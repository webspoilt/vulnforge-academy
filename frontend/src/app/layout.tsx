import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "@/components/ui/toaster";
import { ThemeProvider } from "next-themes";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: "VulnForge Academy - Learn Ethical Hacking",
  description: "A deliberately vulnerable web application designed for cybersecurity training. Master 20 progressive levels covering OWASP Top 10, SQL Injection, XSS, IDOR, SSRF, RCE & more.",
  keywords: ["VulnForge", "cybersecurity", "ethical hacking", "CTF", "SQL injection", "XSS", "OWASP", "bug bounty", "penetration testing"],
  authors: [{ name: "webspoilt" }],
  icons: {
    icon: "/favicon.ico",
  },
  openGraph: {
    title: "VulnForge Academy",
    description: "Learn Hacking by Hacking - Ethically. 20 progressive levels from beginner to nightmare.",
    url: "https://github.com/webspoilt/vulnforge-academy",
    siteName: "VulnForge Academy",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "VulnForge Academy",
    description: "Learn Hacking by Hacking - Ethically. Master OWASP Top 10 vulnerabilities.",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-background text-foreground`}
      >
        <ThemeProvider
          attribute="class"
          defaultTheme="dark"
          enableSystem
          disableTransitionOnChange
        >
          {children}
          <Toaster />
        </ThemeProvider>
      </body>
    </html>
  );
}
