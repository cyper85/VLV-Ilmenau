\version "2.16.2"

\layout {
	indent = 0.0\cm
	\context {
		\Score
		\remove "Bar_number_engraver"
	}
}

\header {
	title = "Meine Hoffung, meine Freude"
	composer = "Jacques Berthier"
	poet = "Taizé"
	copyright = "Les Presses de Taizé"
}

% Komplexes System etwas aufgebrochen
global = {
	\autoBeamOff
	\key d \minor % D-Moll
	\time 3/4
}

SopranNoten = \relative c'' {
	\repeat volta 2 { % normale Wiederholung
	\partial 4 f8 g8
		a4 a8 a8 g8 f8
		d4 c4 f8 g8
		a4. a8 f4
		g2 c8 c8
		d4. d8 \tuplet 3/4 {e8[ d8] e8}
		f4. f8 g8 g8
		a4 a8 a8 d8 bes8
		g4. g8 c8 a8
		f4 f8 d8 f8 e8
		f2
	}
	\bar "|."
}
AltNoten = \relative c'' {
	\repeat volta 2 { % normale Wiederholung
	\partial 4 d8 e8
		f4 f8 f8 c8 c8
		d4 c4 d8 d8
		f4. f8 d4
		e2 c8 c8
		d4. d8 \tuplet 3/2 {cis8[ b8] cis8}
		d4. d8 e8 e8
		f4 f8 f8 g8 g8
		e4. e8 e8 e8
		d4 d8 d8 d8 c8
		c2
	}
	\bar "|."
}
TenorNoten = \relative c' {
	\repeat volta 2 { % normale Wiederholung
	\partial 4 a8 c8
		c4 c8 c8 f8 f8
		f4 e4 a8 c8
		c4. d8 c4
		g2 c,8 c8
		f4. f8 \tuplet 3/2 {a8[ a8] a8}
		a4. a8 c8 c8
		c4 c8 c8 d8 d8
		c4. c8 c8 c8
		a4 a8 f8 a8 g8
		a2
	}
	\bar "|."
}
BassNoten = \relative c' {
	\repeat volta 2 { % normale Wiederholung
	\partial 4 d8 c8
		f4 f8 f8 a,8 a8
		bes4 c4 d8 c8
		<f f,>4. <f f,>8 a,4
		c2 c8 c8
		bes4. bes8 \tuplet 3/2 {a8[ a8] a8}
		d4. d8 c8 c8
		f4 f8 f8 bes,8 bes8
		c4. c8 a8 a8
		d4 d8 d8 bes8 c8
		<f f,>2
	}
	\bar "|."
}

Text = \lyricmode {
	Mei -- ne Hoff -- nung und mei -- ne Freu -- de,
	mei -- ne Stär -- ke, mein Licht:
	Chris -- tus, mei -- ne Zu -- ver -- sicht,
	auf dich ver -- trau ich und fürcht mich nicht,
	auf dich ver -- trau ich und fürcht mich nicht.
}

\score {
<<

	\chords {
		\set chordChanges = ##t
		d8:m c8
		f2 f4/a
		bes4 c4 d4:m c4
		f2 f4:6
		c2.
		bes2 a4:7
		d2:m c4
		f2 g4
		c2 a4:m
		d2:m b8 c8
		f2
	}
                \new ChoirStaff <<
						\new Staff <<
                                \new Voice  { \voiceOne << \global \SopranNoten >> }
                                \new Voice  { \voiceTwo << \global \AltNoten >> }
                                \addlyrics { \Text }
                        >>
                        \new Staff  <<
                                \new Voice  { \voiceOne << \global \clef bass  \TenorNoten >> }
                                \new Voice  { \voiceTwo << \global \clef bass  \BassNoten >> }
                        >>
                >>
        >>
  \layout {}
  \midi {}
}
